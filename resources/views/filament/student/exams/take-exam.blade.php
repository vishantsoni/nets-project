<x-filament-panels::page>
    <div x-data="takeExam()">
        <div x-init="initExam()" class="mb-4 flex justify-between items-center p-4 bg-white rounded-lg shadow">
            <div>
                <h2 class="text-xl font-bold">{{ $exam->title }}</h2>
                <p class="text-sm text-gray-600">Test ID: {{ $exam->test_id }}</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-primary-600" x-text="formatTime(timeLeft)"></div>
                <div class="text-sm text-gray-500">Time Remaining</div>
            </div>
        </div>

        <div x-show="currentStep === 'instructions'" class="mb-6">
            <div class="p-6 bg-white rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Examination Instructions</h3>
                <ul class="list-disc list-inside space-y-2 text-gray-700">
                    <li>Total Duration: {{ $exam->duration }} minutes</li>
                    <li>Maximum Marks: Calculated based on questions</li>
                    @if($exam->negative_marking)
                        <li>Negative marking is enabled ({{ $exam->negative_marks_ratio ?? 0.25 }} per wrong answer)</li>
                    @endif
                    <li>Once started, the exam cannot be paused</li>
                    <li>Navigate between questions using the Previous/Next buttons</li>
                    <li>Your responses will be auto-saved as you progress</li>
                </ul>
                <button @click="startExam()" class="mt-6 px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Start Exam
                </button>
            </div>
        </div>

        <div x-show="currentStep === 'exam'" style="display: none;" class="space-y-6">
            <template x-for="(question, index) in visibleQuestions" :key="question.id">
                <div :class="{'hidden': index !== currentQuestion, 'block': index === currentQuestion}">
                    <div class="p-6 bg-white rounded-lg shadow">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-medium" x-text="`Question ${index + 1}: ${question.question_text}`"></h3>
                            <span class="text-sm text-gray-500" x-text="`${question.marks} mark(s)`"></span>
                        </div>

                        <template x-if="question.type === 'mcq'">
                            <div class="space-y-3">
                                <template x-for="option in getShuffledOptions(question)" :key="option.id">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="radio"
                                               :name="`question_${question.id}`"
                                               :value="option.id"
                                               :checked="answers[question.id] === option.id"
                                               @change="selectAnswer(question.id, option.id)"
                                               class="form-radio">
                                        <span x-text="option.option_text"></span>
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
                                <input type="number"
                                       x-model.number="answers[question.id]"
                                       class="w-32 px-3 py-2 border rounded-lg"
                                       placeholder="Enter answer">
                            </div>
                        </template>

                        <template x-if="question.type === 'fill_blank'">
                            <div>
                                <input type="text"
                                       x-model="answers[question.id]"
                                       class="w-full px-3 py-2 border rounded-lg"
                                       placeholder="Enter your answer">
                            </div>
                        </template>

                        <template x-if="question.type === 'essay'">
                            <div>
                                <textarea x-model="answers[question.id]"
                                          class="w-full px-3 py-2 border rounded-lg"
                                          rows="4"
                                          placeholder="Enter your answer"></textarea>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <div class="flex justify-between items-center mt-6">
                <button @click="prevQuestion()" :disabled="currentQuestion === 0" class="px-4 py-2 bg-gray-100 rounded-lg disabled:opacity-50">
                    Previous
                </button>

                <div class="flex space-x-2 overflow-x-auto max-w-xs">
                    <template x-for="(question, index) in visibleQuestions" :key="question.id">
                        <button @click="currentQuestion = index"
                                :class="{'bg-primary-600 text-white': answers[question.id] !== undefined,
                                        'bg-gray-100': answers[question.id] === undefined,
                                        'ring-2 ring-primary-500': index === currentQuestion}"
                                x-text="index + 1"
                                class="w-10 h-10 rounded-lg flex items-center justify-center">
                        </button>
                    </template>
                </div>

                <button @click="nextQuestion()" :disabled="currentQuestion === visibleQuestions.length - 1" class="px-4 py-2 bg-gray-100 rounded-lg disabled:opacity-50">
                    Next
                </button>
            </div>

            <div class="flex justify-between mt-6">
                <button @click="submitExam()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Submit Exam
                </button>
            </div>
        </div>
    </div>

    <script>
    function takeExam() {
        return {
            currentStep: 'instructions',
            currentQuestion: 0,
            timeLeft: {{ $exam->duration }} * 60,
            questions: @json($getQuestions()),
            attemptId: {{ $getAttemptId() }},
            answers: {},
            timer: null,

            initExam() {
                this.questions = @json($getQuestions());
                if ({{ $isShuffleQuestions() ? 'true' : 'false' }}) {
                    this.questions = this.shuffleArray([...this.questions]);
                }
            },

            startExam() {
                this.currentStep = 'exam';
                this.startTimer();
            },

            startTimer() {
                this.timer = setInterval(() => {
                    this.timeLeft--;
                    if (this.timeLeft <= 0) {
                        this.submitExam();
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
                if ({{ $isShuffleOptions() ? 'true' : 'false' }}) {
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

            saveAnswer(questionId, value) {
                axios.post('/student/exams/save-answer', {
                    attempt_id: this.attemptId,
                    question_id: questionId,
                    answer: value
                }).catch(() => {});
            },

            prevQuestion() {
                if (this.currentQuestion > 0) this.currentQuestion--;
            },

            nextQuestion() {
                if (this.currentQuestion < this.questions.length - 1) this.currentQuestion++;
            },

            submitExam() {
                clearInterval(this.timer);
                axios.post('/student/exams/submit', {
                    attempt_id: this.attemptId
                }).then(() => {
                    window.location.reload();
                }).catch(() => {
                    window.location.reload();
                });
            }
        }
    }
    </script>
</x-filament-panels::page>
