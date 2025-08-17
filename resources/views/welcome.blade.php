<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Thesis Repository System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-900">
                        <span class="text-blue-600">Thesis</span>Repository
                    </h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    Student Thesis Repository System
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-blue-100 max-w-3xl mx-auto">
                    Discover, explore, and manage academic research with our comprehensive thesis repository platform
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100">
                            Get Started
                        </a>
                        <a href="#features" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600">
                            Learn More
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Powerful Features
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Everything you need to manage and explore academic research effectively
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Comprehensive Library</h3>
                    <p class="text-gray-600">Access thousands of thesis documents from various departments and programs in one centralized location.</p>
                </div>
                
                <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Advanced Search</h3>
                    <p class="text-gray-600">Find relevant research quickly with powerful search filters by department, year, keywords, and more.</p>
                </div>
                
                <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Secure Access</h3>
                    <p class="text-gray-600">Role-based access control ensures that students and faculty have appropriate permissions for viewing and managing content.</p>
                </div>
                
                <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Easy Management</h3>
                    <p class="text-gray-600">Simple and intuitive interface for uploading, organizing, and managing thesis documents and metadata.</p>
                </div>
                
                <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Analytics & Reports</h3>
                    <p class="text-gray-600">Track usage statistics, popular research topics, and generate comprehensive reports for academic insights.</p>
                </div>
                
                <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Mobile Responsive</h3>
                    <p class="text-gray-600">Access the repository from any device with our fully responsive design that works seamlessly on desktop and mobile.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                        About Our Repository System
                    </h2>
                    <p class="text-lg text-gray-600 mb-6">
                        Our Student Thesis Repository System is designed to streamline academic research management and discovery. Built with modern technology and user experience in mind, it serves as a comprehensive platform for students, faculty, and researchers.
                    </p>
                    <p class="text-lg text-gray-600 mb-8">
                        Whether you're a student looking for research inspiration, a faculty member supervising thesis work, or an administrator managing academic content, our system provides the tools you need to succeed.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 text-center">
                                Access Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 text-center">
                                Get Started
                            </a>
                        @endauth
                    </div>
                </div>
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl p-8 text-white">
                    <h3 class="text-2xl font-bold mb-6">Key Statistics</h3>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold mb-2">1000+</div>
                            <div class="text-blue-100">Thesis Documents</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold mb-2">50+</div>
                            <div class="text-blue-100">Departments</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold mb-2">5000+</div>
                            <div class="text-blue-100">Active Users</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold mb-2">99.9%</div>
                            <div class="text-blue-100">Uptime</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-blue-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                Ready to Explore Academic Research?
            </h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                Join thousands of students and faculty members who are already using our platform to discover and manage academic research.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="{{ url('/dashboard') }}" class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100">
                        Start Exploring
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-blue-600">
                            Create Account
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h3 class="text-xl font-bold mb-4">
                    <span class="text-blue-400">Thesis</span>Repository
                </h3>
                <p class="text-gray-400 mb-6 max-w-md mx-auto">
                    Empowering academic research through innovative technology and comprehensive thesis management solutions.
                </p>
                <div class="border-t border-gray-800 pt-6">
                    <p class="text-gray-400 text-sm">
                        © {{ date('Y') }} Thesis Repository System. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
