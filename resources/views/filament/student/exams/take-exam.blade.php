<x-filament-panels::page>
    <div x-data="takeExam()">
        <div x-init="initExam()"
            class="mb-4 flex justify-between items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $exam->title }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Test ID: {{ $exam->test_id }}</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-primary-600" x-text="formatTime(timeLeft)"></div>
                <div class="text-sm text-gray-500">Time Remaining</div>
            </div>
        </div>

        <div x-show="currentStep === 'instructions'" class="mb-6">
            <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Examination Instructions</h3>
                @if(filled($exam->description))
                    <div class="prose prose-sm dark:prose-invert max-w-none mb-5 text-gray-600 dark:text-gray-300">
                        {!! str($exam->description)->sanitizeHtml() !!}
                    </div>
                @endif
                <ul class="list-disc list-inside space-y-2 text-gray-500 dark:text-gray-100">
                    <li>Total Duration: {{ $exam->duration }} minutes</li>
                    <li>Maximum Marks: Calculated based on questions</li>
                    @if($exam->negative_marking)
                        <li>Negative marking is enabled ({{ $exam->negative_marks_ratio ?? 0.25 }} per wrong answer)</li>
                    @endif
                    <li>Once started, the exam cannot be paused</li>
                    <li>Navigate between questions using the Previous/Next buttons</li>
                    <li>Your responses will be auto-saved as you progress</li>
                </ul>
                <button @click="startExam()"
                    class="mt-6 px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Start Exam
                </button>
            </div>
        </div>

        <div x-show="currentStep === 'exam'" style="display: none;" class="space-y-6">
            <div class="rounded-xl bg-white p-4 shadow dark:bg-gray-800">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Exam Sections</h3>
                    <span class="text-sm text-gray-500 mt-2" x-text="`${sectionGroups.length} section(s)`"></span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <template x-for="section in sectionGroups" :key="section.key">
                        <button type="button" @click="currentQuestion = section.firstQuestion"
                            :class="currentSectionKey === section.key
                                    ? 'bg-primary-600 text-white ring-2 ring-primary-300'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'"
                            class="rounded-lg px-4 py-2 text-sm font-medium transition">
                            <span x-text="section.title"></span>
                            <span class="ml-1 opacity-75" x-text="`(${section.count})`"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="rounded-xl bg-white p-4 shadow dark:bg-gray-800">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Current Section</h3>
                    <h2 class="mt-1 text-lg font-bold text-gray-900 dark:text-gray-100"
                        x-text="currentSectionTitle"></h2>
                </div>
            </div>

            <template x-for="(question, index) in visibleQuestions" :key="question.id">
                <div :class="{'hidden': index !== currentQuestion, 'block': index === currentQuestion}">
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div x-show="question.subject || question.chapter || question.topic"
                            class="mb-5 flex flex-wrap gap-2">
                            <template x-if="question.subject">
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/50 dark:text-blue-200"
                                    x-text="`Subject: ${question.subject}`"></span>
                            </template>
                            <template x-if="question.chapter">
                                <span class="inline-flex items-center rounded-full bg-violet-100 px-3 py-1 text-xs font-medium text-violet-800 dark:bg-violet-900/50 dark:text-violet-200"
                                    x-text="`Chapter: ${question.chapter}`"></span>
                            </template>
                            <template x-if="question.topic">
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200"
                                    x-text="`Topic: ${question.topic}`"></span>
                            </template>
                        </div>
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                <span data-math-content
                                    x-text="`Question ${index + 1}: ${question.question_text}`"></span>
                            </h3>
                            <span class="shrink-0 text-sm text-gray-500" x-text="`${question.marks} mark(s)`"></span>
                        </div>

                        <template x-if="question.type === 'mcq'">
                            <div class="space-y-3">
                                <template x-for="option in getShuffledOptions(question)" :key="option.id">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="radio" :name="`question_${question.id}`" :value="option.id"
                                            :checked="answers[question.id] === option.id"
                                            @change="selectAnswer(question.id, option.id)" class="form-radio">
                                        <span data-math-content x-text="option.option_text"></span>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <template x-if="question.type === 'true_false'">
                            <div class="space-y-3">
                                <label class="flex items-center space-x-3">
                                    <input type="radio" name="question_true_false" value="true"
                                        :checked="answers[question.id] === true"
                                        @change="selectAnswer(question.id, true)" class="form-radio">
                                    <span>True</span>
                                </label>
                                <label class="flex items-center space-x-3">
                                    <input type="radio" name="question_true_false" value="false"
                                        :checked="answers[question.id] === false"
                                        @change="selectAnswer(question.id, false)" class="form-radio">
                                    <span>False</span>
                                </label>
                            </div>
                        </template>

                        <template x-if="question.type === 'integer'">
                            <div>
                                <input type="number" x-model.number="answers[question.id]"
                                    class="w-32 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Enter answer">
                            </div>
                        </template>

                        <template x-if="question.type === 'fill_blank'">
                            <div>
                                <input type="text" x-model="answers[question.id]"
                                    class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Enter your answer">
                            </div>
                        </template>

                        <template x-if="question.type === 'essay'">
                            <div>
                                <textarea x-model="answers[question.id]" class="w-full px-3 py-2 border rounded-lg"
                                    rows="4" placeholder="Enter your answer"></textarea>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <div class="flex justify-between items-center mt-6">
                <button @click="prevQuestion()" :disabled="currentQuestion === 0"
                    class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg disabled:opacity-50">
                    Previous
                </button>

                <div class="flex space-x-2 overflow-x-auto max-w-xs">
                    <template x-for="(question, index) in visibleQuestions" :key="question.id">
                        <button @click="currentQuestion = index" :class="{'bg-primary-600 text-white': answers[question.id] !== undefined,
                                        'bg-gray-100': answers[question.id] === undefined,
                                        'ring-2 ring-primary-500': index === currentQuestion}" x-text="index + 1"
                            class="w-10 h-10 rounded-lg flex items-center justify-center">
                        </button>
                    </template>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="nextQuestion()" :disabled="currentQuestion === visibleQuestions.length - 1"
                        class="px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg disabled:opacity-50">
                        Next
                    </button>
                    <button type="button" @click="submitExam()" :disabled="isSubmitting"
                        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50"
                        x-text="isSubmitting ? 'Submitting...' : 'Submit Exam'">
                    </button>
                </div>
            </div>
            <p x-show="submissionError" x-text="submissionError" class="mt-3 text-sm text-danger-600" role="alert"></p>
        </div>
    </div>

    <script>
        function takeExam() {
            return {
                currentStep: 'instructions',
                currentQuestion: 0,
                timeLeft: {{ $exam->duration }} * 60,
                questions: @json($this->getQuestions()),
                attemptId: {{ $this->getAttemptId() }},
                answers: {},
                timer: null,
                isSubmitting: false,
                submissionError: '',
                allowExit: false,
                beforeUnloadHandler: null,
                navigationHandler: null,
                popStateHandler: null,

                get sectionGroups() {
                    const groups = new Map();
                    this.questions.forEach((question, index) => {
                        const key = question.section_id === null ? 'unsectioned' : String(question.section_id);
                        if (!groups.has(key)) {
                            groups.set(key, {
                                key,
                                title: question.section || 'Unsectioned questions',
                                firstQuestion: index,
                                count: 0,
                            });
                        }
                        groups.get(key).count++;
                    });
                    return [...groups.values()];
                },

                get currentSectionKey() {
                    const question = this.questions[this.currentQuestion];
                    return question
                        ? (question.section_id === null ? 'unsectioned' : String(question.section_id))
                        : null;
                },

                get currentSectionTitle() {
                    return this.questions[this.currentQuestion]?.section || 'Unsectioned questions';
                },

                initExam() {
                    this.questions = @json($this->getQuestions());
                    if ({{ $this->isShuffleQuestions() ? 'true' : 'false' }}) {
                        const groupedQuestions = new Map();
                        this.questions.forEach((question) => {
                            const key = question.section_id === null ? 'unsectioned' : String(question.section_id);
                            if (!groupedQuestions.has(key)) groupedQuestions.set(key, []);
                            groupedQuestions.get(key).push(question);
                        });
                        this.questions = [...groupedQuestions.values()]
                            .flatMap((questions) => this.shuffleArray(questions));
                    }

                    this.beforeUnloadHandler = (event) => {
                        if (this.currentStep === 'exam' && !this.allowExit && !this.isSubmitting) {
                            event.preventDefault();
                            event.returnValue = '';
                        }
                    };
                    this.navigationHandler = (event) => {
                        const link = event.target.closest('a[href]');
                        if (!link || this.currentStep !== 'exam' || this.allowExit || this.isSubmitting) {
                            return;
                        }

                        if (!window.confirm('Your exam is in progress. Are you sure you want to leave?')) {
                            event.preventDefault();
                            event.stopImmediatePropagation();
                            return;
                        }

                        this.allowExit = true;
                    };
                    this.popStateHandler = () => {
                        if (this.currentStep !== 'exam' || this.allowExit || this.isSubmitting) {
                            return;
                        }

                        if (window.confirm('Your exam is in progress. Are you sure you want to leave?')) {
                            this.allowExit = true;
                            return;
                        }

                        window.history.pushState({ examInProgress: true }, '', window.location.href);
                    };

                    window.addEventListener('beforeunload', this.beforeUnloadHandler);
                    document.addEventListener('click', this.navigationHandler, true);
                    window.addEventListener('popstate', this.popStateHandler);
                },

                startExam() {
                    this.currentStep = 'exam';
                    window.history.pushState({ examInProgress: true }, '', window.location.href);
                    this.startTimer();
                },

                startTimer() {
                    this.timer = setInterval(() => {
                        this.timeLeft--;
                        if (this.timeLeft <= 0) {
                            this.submitExam(false);
                        }
                    }, 1000);
                },

                formatTime(seconds) {
                    const m = Math.floor(seconds / 60);
                    const s = seconds % 60;
                    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                },

                get visibleQuestions() {
                    return this.questions;
                },

                getShuffledOptions(question) {
                    let opts = [...question.options];
                    if ({{ $this->isShuffleOptions() ? 'true' : 'false' }}) {
                        opts = this.shuffleArray(opts);
                    }
                    return opts;
                },

                shuffleArray(arr) {
                    for (let i = arr.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [arr[i], arr[j]] = [arr[j], arr[i]];
                    }
                    return arr;
                },

                selectAnswer(questionId, value) {
                    this.answers[questionId] = value;
                    this.saveAnswer(questionId, value);
                },

                // saveAnswer(questionId, value) {
                //     window.axios.post('/student/exams/save-answer', {
                //         attempt_id: this.attemptId,
                //         question_id: questionId,
                //         answer: value
                //     }).catch(() => {});
                // },
                saveAnswer(questionId, value) {
                    fetch('/student/exams/save-answer', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            attempt_id: this.attemptId,
                            question_id: questionId,
                            answer: value
                        })
                    }).catch(() => { });
                },

                prevQuestion() {
                    if (this.currentQuestion > 0) this.currentQuestion--;
                },

                nextQuestion() {
                    if (this.currentQuestion < this.questions.length - 1) this.currentQuestion++;
                },

                // submitExam(confirmSubmission = true) {
                //     if (this.isSubmitting || (confirmSubmission && !window.confirm('Submit your exam now?'))) {
                //         return;
                //     }

                //     this.isSubmitting = true;
                //     this.submissionError = '';
                //     clearInterval(this.timer);
                //     this.timer = null;
                //     window.axios.post('/student/exams/submit', {
                //         attempt_id: this.attemptId
                //     }, {
                //         headers: {
                //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                //         },
                //         timeout: 15000
                //     }).then((response) => {
                //         if (! response.data.redirect_url) {
                //             throw new Error('No result URL was returned.');
                //         }

                //         window.location.href = response.data.redirect_url;
                //     }).catch(() => {
                //         this.isSubmitting = false;
                //         this.submissionError = 'Unable to submit the exam. Please try again.';
                //         this.startTimer();
                //     });
                // }
                submitExam(confirmSubmission = true) {
                    if (this.isSubmitting || (confirmSubmission && !window.confirm('Submit your exam now?'))) {
                        return;
                    }

                    this.isSubmitting = true;
                    this.submissionError = '';
                    clearInterval(this.timer);
                    this.timer = null;

                    fetch('/student/exams/submit', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            attempt_id: this.attemptId
                        })
                    })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.json();
                        })
                        .then((data) => {
                            if (!data.redirect_url) {
                                throw new Error('No result URL was returned.');
                            }
                            this.allowExit = true;
                            window.location.href = data.redirect_url;
                        }).catch(() => {
                            this.isSubmitting = false;
                            this.submissionError = 'Unable to submit the exam. Please try again.';
                            this.startTimer();
                        });
                }
            }
        }
    </script>
</x-filament-panels::page>