<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrinaCrud Permissions Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    @livewireStyles
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="bg-gray-800 text-white p-4">
            <div class="container mx-auto">
                <h1 class="text-xl font-bold">TrinaCrud Admin</h1>
            </div>
        </nav>
        
        <livewire:trina-crud::permissions-manager />
    </div>
    
    @livewireScripts
</body>
</html>
