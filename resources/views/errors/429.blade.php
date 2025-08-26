<x-guest-layout>
    <div class="text-center">
        <!-- Icon -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-orange-100 mb-6">
            <svg class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <!-- Title -->
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Too Many Requests</h2>
        
        <!-- Message -->
        <div class="mb-6">
            <p class="text-gray-600 mb-4">
                You've made too many requests. Please try again later.
            </p>
            <p class="text-sm text-gray-500">
                This helps us maintain system performance for all users.
            </p>
        </div>

        <!-- Countdown Timer -->
        <div class="mb-6">
            <div class="text-3xl font-mono font-bold text-orange-600" id="countdown">01:00</div>
            <p class="text-sm text-gray-500 mt-2">Time until you can try again</p>
        </div>

        <!-- Retry Button -->
        <div class="space-y-4">
            <button onclick="window.history.back()" id="retryBtn" disabled class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Try Again
            </button>
        </div>

        <script>
        let timeLeft = 60;
        const countdown = document.getElementById('countdown');
        const retryBtn = document.getElementById('retryBtn');
        
        function preventBack() {
            history.pushState(null, null, location.href);
        }
        
        function blockNavigation(e) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
        
        function blockKeys(e) {
            if ((e.altKey && e.keyCode === 37) || (e.keyCode === 8)) {
                e.preventDefault();
            }
        }
        
        preventBack();
        window.addEventListener('popstate', preventBack);
        window.addEventListener('beforeunload', blockNavigation);
        document.addEventListener('keydown', blockKeys);
        
        const timer = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdown.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                retryBtn.disabled = false;
                retryBtn.className = 'w-full inline-flex justify-center items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150';
                countdown.textContent = '00:00';
                window.removeEventListener('popstate', preventBack);
                window.removeEventListener('beforeunload', blockNavigation);
                document.removeEventListener('keydown', blockKeys);
            }
            timeLeft--;
        }, 1000);
        </script>

        <!-- Info Box -->
        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Rate Limiting Active</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Please wait a moment before making additional requests to ensure optimal system performance.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>