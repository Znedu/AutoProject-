<x-mail::message>
# Verify Your Email Address

Hello **{{ $name }}**,

Thank you for registering with **AutoProject-D Custom Garage**. Please enter the 6-digit verification code below to activate your account:

<x-mail::panel>
<div style="font-size: 34px; font-weight: 800; letter-spacing: 10px; text-align: center; color: #E63946; padding: 12px 0;">
{{ $code }}
</div>
</x-mail::panel>

This code is valid for **15 minutes**. For security reasons, please do not share this code with anyone.

If you did not request this account registration, no further action is required.

Best regards,<br>
**AutoProject+ Team**<br>
*AutoProject-D Custom Garage*
</x-mail::message>
