<footer class="bg-gray-900 text-white py-12 mt-16">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <h3 class="text-lg font-bold mb-4">NETS</h3>
                <p class="text-gray-400 text-sm">National Education Technology System - Comprehensive study materials, examinations, and e-learning platform.</p>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Quick Links</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-white">Home</a></li>
                    <li><a href="{{ route('store') }}" class="text-gray-400 hover:text-white">Store</a></li>
                    <li><a href="{{ route('b2b-enquiry') }}" class="text-gray-400 hover:text-white">B2B Enquiry</a></li>
                    <li><a href="{{ route('contact') }}" class="text-gray-400 hover:text-white">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Services</h4>
                <ul class="space-y-2 text-sm">
                    <li><span class="text-gray-400">Online Examinations</span></li>
                    <li><span class="text-gray-400">Study Materials</span></li>
                    <li><span class="text-gray-400">Question Banks</span></li>
                    <li><span class="text-gray-400">OMR Sheets</span></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Contact</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li>Email: info@nets.com</li>
                    <li>Phone: 1800-123-4567</li>
                    <li>Delhi, India</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-400">
            &copy; {{ date('Y') }} NETS. All rights reserved.
        </div>
    </div>
</footer>
