@component('mail::message')
Hello {{$user->name}},

<p>We Understand what Happens.</p>

@component('mail::button', ['url' => $frontend_url])
Reset Password

@endcomponent

<p>In case you have any issues recovering password, please contact us</p>

@endcomponent