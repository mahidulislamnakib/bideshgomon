#!/bin/bash
# Blue-Green Deployment Script
# This provides an alternative to canary deployments with instant traffic switch

set -e

# Configuration
NAMESPACE="production"
APP_NAME="bideshgomon"
NEW_IMAGE=$1
HEALTH_CHECK_URL="/health"
SMOKE_TEST_RETRIES=12
SMOKE_TEST_DELAY=5

if [ -z "$NEW_IMAGE" ]; then
  echo "❌ Usage: $0 <new-image-tag>"
  exit 1
fi

echo "🚀 Starting Blue-Green deployment for $APP_NAME"
echo "   New image: $NEW_IMAGE"

# Determine current color
CURRENT_COLOR=$(kubectl get service $APP_NAME -n $NAMESPACE -o jsonpath='{.spec.selector.color}' 2>/dev/null || echo "blue")
if [ "$CURRENT_COLOR" == "blue" ]; then
  NEW_COLOR="green"
else
  NEW_COLOR="blue"
fi

echo "   Current: $CURRENT_COLOR → New: $NEW_COLOR"

# 1. Deploy new version to inactive color
echo "📦 Deploying new version to $NEW_COLOR..."
kubectl set image deployment/$APP_NAME-$NEW_COLOR app=$NEW_IMAGE -n $NAMESPACE

# Wait for deployment to be ready
echo "⏳ Waiting for $NEW_COLOR deployment..."
kubectl rollout status deployment/$APP_NAME-$NEW_COLOR -n $NAMESPACE --timeout=5m

# 2. Get the new deployment endpoint
NEW_POD_IP=$(kubectl get pod -l app=$APP_NAME,color=$NEW_COLOR -n $NAMESPACE -o jsonpath='{.items[0].status.podIP}')
echo "   New pod IP: $NEW_POD_IP"

# 3. Run smoke tests against new deployment
echo "🧪 Running smoke tests against $NEW_COLOR..."
for i in $(seq 1 $SMOKE_TEST_RETRIES); do
  if curl -f -s "http://$NEW_POD_IP:8000$HEALTH_CHECK_URL" > /dev/null; then
    echo "   ✅ Health check passed ($i/$SMOKE_TEST_RETRIES)"
    break
  fi
  
  if [ $i -eq $SMOKE_TEST_RETRIES ]; then
    echo "   ❌ Health check failed after $SMOKE_TEST_RETRIES attempts"
    echo "   Rolling back..."
    kubectl rollout undo deployment/$APP_NAME-$NEW_COLOR -n $NAMESPACE
    exit 1
  fi
  
  echo "   Waiting for health check... ($i/$SMOKE_TEST_RETRIES)"
  sleep $SMOKE_TEST_DELAY
done

# 4. Run additional smoke tests
echo "🧪 Running API smoke tests..."
API_HEALTH=$(curl -s "http://$NEW_POD_IP:8000/api/health" | jq -r '.status' 2>/dev/null || echo "unhealthy")
if [ "$API_HEALTH" != "healthy" ]; then
  echo "   ❌ API health check failed: $API_HEALTH"
  kubectl rollout undo deployment/$APP_NAME-$NEW_COLOR -n $NAMESPACE
  exit 1
fi
echo "   ✅ API health check passed"

# 5. Switch traffic to new version (atomic operation)
echo "🔄 Switching traffic from $CURRENT_COLOR to $NEW_COLOR..."
kubectl patch service $APP_NAME -n $NAMESPACE -p "{\"spec\":{\"selector\":{\"color\":\"$NEW_COLOR\"}}}"

echo "   ✅ Traffic switched successfully"

# 6. Monitor new version for 30 seconds
echo "👀 Monitoring new version for 30 seconds..."
sleep 30

# Check metrics
ERROR_COUNT=$(kubectl logs -l app=$APP_NAME,color=$NEW_COLOR -n $NAMESPACE --tail=100 | grep -ci "error" || echo "0")
if [ "$ERROR_COUNT" -gt 10 ]; then
  echo "   ⚠️  High error count detected: $ERROR_COUNT"
  echo "   Rolling back to $CURRENT_COLOR..."
  kubectl patch service $APP_NAME -n $NAMESPACE -p "{\"spec\":{\"selector\":{\"color\":\"$CURRENT_COLOR\"}}}"
  exit 1
fi

echo "   ✅ New version looks healthy (errors: $ERROR_COUNT)"

# 7. Keep old version running for quick rollback
echo "📝 Old version ($CURRENT_COLOR) kept running for quick rollback"
echo "   To rollback: kubectl patch service $APP_NAME -n $NAMESPACE -p '{\"spec\":{\"selector\":{\"color\":\"$CURRENT_COLOR\"}}}'"

# 8. Scale down old version after verification period (optional)
read -p "Scale down old version ($CURRENT_COLOR) now? (y/N) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
  echo "⬇️  Scaling down $CURRENT_COLOR..."
  kubectl scale deployment/$APP_NAME-$CURRENT_COLOR --replicas=0 -n $NAMESPACE
  echo "   ✅ Old version scaled down"
fi

echo "✅ Blue-Green deployment completed successfully!"
echo ""
echo "Summary:"
echo "  • New version ($NEW_COLOR): ACTIVE"
echo "  • Old version ($CURRENT_COLOR): $([ "$REPLY" = "y" ] && echo "SCALED DOWN" || echo "STANDBY")"
echo "  • Image: $NEW_IMAGE"
