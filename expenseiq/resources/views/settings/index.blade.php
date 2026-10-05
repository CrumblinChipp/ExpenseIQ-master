<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - ExpenseIQ</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    referrerpolicy="no-referrer" />
</head>

<body class="bg-[#FFFBEA]">

<div class="flex min-h-screen">

    @include('settings.sidebar')

    @if($page == 'personal')
        @include('settings.personal')
    @elseif($page == 'password')
        @include('settings.password')
    @elseif($page == 'danger')
        @include('settings.danger')
    @endif

</div>

@include('settings.modals.nickname')
@include('settings.modals.fullname')
@include('settings.modals.email')
@include('settings.modals.budget')
@include('settings.modals.clear-data')
@include('settings.modals.delete-account')

</body>
</html>