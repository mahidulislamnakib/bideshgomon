<x-mail::message>
# You're Invited to Join {{ $agencyName }}!

Hello {{ $teamMember->name }},

You have been invited to join **{{ $agencyName }}** as a **{{ ucfirst($teamMember->role) }}** on BideshGomon platform.

## Your Invitation Details:

- **Position**: {{ ucfirst($teamMember->role) }}
- **Commission Rate**: {{ $teamMember->commission_rate }}%
- **Agency**: {{ $agencyName }}

<x-mail::button :url="$invitationUrl">
Accept Invitation & Register
</x-mail::button>

This invitation link is valid and will expire once used. Please click the button above to accept the invitation and complete your registration.

## What's Next?

1. Click the invitation link above
2. Complete your registration
3. Start working with {{ $agencyName }}
4. Help Bangladeshi citizens with their visa applications

---

**Need help?** Contact {{ $agencyName }} directly or reach out to our support team.

Thanks,<br>
{{ config('app.name') }} Team

<x-mail::subcopy>
If you're having trouble clicking the "Accept Invitation & Register" button, copy and paste the URL below into your web browser:
{{ $invitationUrl }}
</x-mail::subcopy>
</x-mail::message>
