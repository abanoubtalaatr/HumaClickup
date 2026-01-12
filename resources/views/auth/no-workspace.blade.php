<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Waiting for Invitation - HumaClickup</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-indigo-100 via-white to-purple-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center">
            <!-- Logo -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-indigo-600">HumaClickup</h1>
            </div>

            <!-- Welcome Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <!-- Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 mb-6">
                    <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-gray-900 mb-4">Welcome, {{ auth()->user()->name }}!</h2>
                
                <p class="text-gray-600 mb-6">
                    Your account has been created successfully. You're currently not a member of any workspace.
                </p>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">What happens next?</h4>
                    <p class="text-sm text-blue-700">
                        A workspace administrator needs to invite you using your email address: 
                        <strong>{{ auth()->user()->email }}</strong>
                    </p>
                </div>

                <p class="text-sm text-gray-500 mb-6">
                    Once you're invited to a workspace, you'll be able to access projects and tasks assigned to you.
                </p>

                <div class="flex flex-col space-y-3">
                    <a href="{{ route('no-workspace') }}" 
                       class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Check for Invitations
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>

            <!-- Info -->
            <p class="mt-6 text-sm text-gray-500">
                Need help? Contact your workspace administrator.
            </p>
        </div>
    </div>
</body>
</html>

