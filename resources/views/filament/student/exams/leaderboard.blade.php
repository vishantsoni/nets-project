<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $exam->title }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Test ID: {{ $exam->test_id }} · Live rankings update as students complete the exam.
                This page refreshes automatically.
            </p>
        </div>

        <div class="overflow-x-auto rounded-xl bg-white shadow dark:bg-gray-800">
            @if($leaderboard->isNotEmpty())
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3">Rank</th>
                            <th class="px-5 py-3">Student</th>
                            <th class="px-5 py-3">Score</th>
                            <th class="px-5 py-3">Percentage</th>
                            <th class="px-5 py-3">Time taken</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaderboard as $student)
                            <tr @class([
                                'border-b border-gray-100 dark:border-gray-700',
                                'bg-primary-50 dark:bg-primary-950/40' => $student['is_current_user'],
                            ])>
                                <td class="px-5 py-4 font-semibold text-gray-900 dark:text-gray-100">#{{ $student['rank'] }}</td>
                                <td class="px-5 py-4 text-gray-700 dark:text-gray-200">
                                    {{ $student['name'] }}
                                    @if($student['is_current_user'])
                                        <span class="ml-2 text-xs font-medium text-primary-600 dark:text-primary-400">You</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-gray-700 dark:text-gray-200">
                                    {{ number_format((float) $student['obtained_marks'], 2) }} /
                                    {{ number_format((float) $student['total_marks'], 2) }}
                                </td>
                                <td class="px-5 py-4 text-gray-700 dark:text-gray-200">{{ number_format((float) $student['percentage'], 2) }}%</td>
                                <td class="px-5 py-4 text-gray-700 dark:text-gray-200">
                                    {{ gmdate('H:i:s', max(0, (int) $student['time_taken'])) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="p-6 text-gray-500 dark:text-gray-400">No completed exam attempts yet.</p>
            @endif
        </div>
    </div>

    <script>
        window.setTimeout(() => window.location.reload(), 15000);
    </script>
</x-filament-panels::page>
