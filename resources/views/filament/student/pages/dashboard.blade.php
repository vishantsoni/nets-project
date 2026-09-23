<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="p-6 bg-white rounded-lg shadow">
            <h3 class="text-sm font-medium text-gray-500">Available Exams</h3>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['availableExams'] }}</p>
        </div>
        <div class="p-6 bg-white rounded-lg shadow">
            <h3 class="text-sm font-medium text-gray-500">Exams Taken</h3>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['takenExams'] }}</p>
        </div>
        <div class="p-6 bg-white rounded-lg shadow">
            <h3 class="text-sm font-medium text-gray-500">Pending Results</h3>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['pendingResults'] }}</p>
        </div>
        <div class="p-6 bg-white rounded-lg shadow">
            <h3 class="text-sm font-medium text-gray-500">Purchased Materials</h3>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['purchasedMaterials'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold mb-3">Available Exams</h3>
            @if($getAvailableExams()->isNotEmpty())
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="pb-2">Exam</th>
                            <th class="pb-2">Subject</th>
                            <th class="pb-2">Duration</th>
                            <th class="pb-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($getAvailableExams() as $exam)
                            <tr class="border-b">
                                <td class="py-2">{{ Str::limit($exam->title, 30) }}</td>
                                <td class="py-2">{{ $exam->subject->name ?? 'N/A' }}</td>
                                <td class="py-2">{{ $exam->duration }} min</td>
                                <td class="py-2">
                                    <a href="{{ route('student.exams.take', $exam->id) }}" class="text-sm text-primary-600 hover:text-primary-900">Take Exam</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500">No exams available right now.</p>
            @endif
        </div>

        <div>
            <h3 class="text-lg font-semibold mb-3">Recent Results</h3>
            @if($getRecentResults()->isNotEmpty())
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="pb-2">Exam</th>
                            <th class="pb-2">Score</th>
                            <th class="pb-2">%</th>
                            <th class="pb-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($getRecentResults() as $result)
                            <tr class="border-b">
                                <td class="py-2">{{ Str::limit($result->attempt->examination->title ?? 'N/A', 30) }}</td>
                                <td class="py-2">{{ $result->obtained_marks }} / {{ $result->total_marks }}</td>
                                <td class="py-2">{{ $result->percentage }}%</td>
                                <td class="py-2">
                                    <a href="{{ route('student.results.view', $result->id) }}" class="text-sm text-primary-600 hover:text-primary-900">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500">No results yet.</p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
