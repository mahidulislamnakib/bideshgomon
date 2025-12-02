/**
 * Image Placeholder Utility for BideshGomon
 * Generates placeholder images until real images are added
 */

export const usePlaceholderImages = () => {
    const API_BASE = 'https://images.unsplash.com/photo-'

    // Country/Destination Images (Unsplash IDs)
    const destinations = {
        usa: `${API_BASE}1485738531670-978f0fea8e7e?w=800&h=600&fit=crop`,
        canada: `${API_BASE}1503614472-8c93d56e92ce?w=800&h=600&fit=crop`,
        australia: `${API_BASE}1523482580247-bcbe36d6b8f5?w=800&h=600&fit=crop`,
        uk: `${API_BASE}1513635269190-d13e44f08f07?w=800&h=600&fit=crop`,
        germany: `${API_BASE}1467269204594-9661b134dd2b?w=800&h=600&fit=crop`,
        uae: `${API_BASE}1512453979798-5ea266f8880c?w=800&h=600&fit=crop`,
        singapore: `${API_BASE}1525625293386-3f8f99389edd?w=800&h=600&fit=crop`,
        malaysia: `${API_BASE}1596422846543-75c6fc197f07?w=800&h=600&fit=crop`,
    }

    // Service Category Images
    const services = {
        visa: `${API_BASE}1436491865332-7a61a109cc05?w=800&h=600&fit=crop`,
        education: `${API_BASE}1523050854058-8df90110c9f1?w=800&h=600&fit=crop`,
        work: `${API_BASE}1454165804606-c3d57bc86b40?w=800&h=600&fit=crop`,
        tourist: `${API_BASE}1488646953014-85cb44e67410?w=800&h=600&fit=crop`,
        migration: `${API_BASE}1560518883-ce09059eeffa?w=800&h=600&fit=crop`,
        business: `${API_BASE}1486406146926-c627a92ad1ab?w=800&h=600&fit=crop`,
        medical: `${API_BASE}1576091160399-112ba8d25d1d?w=800&h=600&fit=crop`,
        attestation: `${API_BASE}1450101499163-c8848c66ca85?w=800&h=600&fit=crop`,
    }

    // Hero/Background Images
    const hero = {
        migration: `${API_BASE}1436491865332-7a61a109cc05?w=1920&h=1080&fit=crop`,
        success: `${API_BASE}1521737852567-6949f3f9f2b5?w=1920&h=1080&fit=crop`,
        team: `${API_BASE}1522071820081-009f0129c71c?w=1920&h=1080&fit=crop`,
    }

    // User Avatars (Diverse, professional)
    const avatars = {
        male1: `${API_BASE}1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&crop=faces`,
        male2: `${API_BASE}1506794778202-cad84cf45f1d?w=200&h=200&fit=crop&crop=faces`,
        male3: `${API_BASE}1500648767791-00dcc994a43e?w=200&h=200&fit=crop&crop=faces`,
        female1: `${API_BASE}1494790108377-be9c29b29330?w=200&h=200&fit=crop&crop=faces`,
        female2: `${API_BASE}1438761681033-6461ffad8d80?w=200&h=200&fit=crop&crop=faces`,
        female3: `${API_BASE}1487412720507-e7ab37603c6f?w=200&h=200&fit=crop&crop=faces`,
    }

    // Fallback for missing images
    const fallback = (width = 800, height = 600, text = 'BideshGomon') => {
        return `https://via.placeholder.com/${width}x${height}/10b981/ffffff?text=${encodeURIComponent(text)}`
    }

    return {
        destinations,
        services,
        hero,
        avatars,
        fallback,
    }
}
