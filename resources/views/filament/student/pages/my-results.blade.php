<x-filament-panels::page>
    @if($getAttempts()->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b">
                        <th class="pb-3">Examination</th>
                        <th class="pb-3">Attempt #</th>
                        <th class="pb-3">Score</th>
                        <th class="pb-3">Percentage</th>
                        <th class="pb-3">Correct</th>
                        <th class="pb-3">Date</th>
                        <th class="pb-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($getAttempts() as $attempt)
                        <tr class="border-b">
                            <td class="py-3">{{ Str::limit($attempt->examination->title ?? 'N/A', 40) }}</td>
                            <td class="py-3">{{ $attempt->attempt_number }}</td>
                            <td class="py-3">{{ $attempt->result->obtained_marks ?? 0 }} / {{ $attempt->result->total_marks ?? 0 }}</td>
                            <td class="py-3">{{ $attempt->result->percentage ?? 0 }}%</td>
                            <td class="py-3">{{ $attempt->result->correct_answers ?? 0 }}</td>
                            <td class="py-3">{{ $attempt->started_at?->format('M d, Y') ?? 'N/A' }}</td>
                            <td class="py-3">
                                @if($attempt->result)
                                    <a href="{{ route('student.results.view', $attempt->result->id) }}" class="text-sm text-primary-600">View</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500">You haven't taken any exams yet.</p>
    @endif
</x-filament-panels::page>
