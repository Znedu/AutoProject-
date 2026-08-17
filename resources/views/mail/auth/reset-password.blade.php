<x-mail::message>
# Reset Your Password

Hello **{{ $name }}**,

You are receiving this email because we received a password reset request for your **AutoProject+** account.

<x-mail::button :url="$url">
Reset Password
</x-mail::button>

This password reset link will expire in **60 minutes**.

If you did not request a password reset, no further action is required and your account remains secure.

Best regards,<br>
**AutoProject+ Team**<br>
*AutoProject-D Custom Garage*
</x-mail::message>
