import 'mathlive';
import 'mathlive/static.css';
import 'mathlive/fonts.css';
import '../css/math-content.css';

const formulaPattern = /\\\(([\s\S]+?)\\\)|\\\[([\s\S]+?)\\\]/g;
const formulaComposers = new WeakMap();

function renderMath(element, content) {
    const value = String(content ?? '');

    element.replaceChildren();

    let position = 0;
    for (const match of value.matchAll(formulaPattern)) {
        const formula = match[1] ?? match[2];
        const index = match.index ?? 0;

        element.append(document.createTextNode(value.slice(position, index)));

        const math = document.createElement('math-span');
        math.textContent = formula;
        if (match[2] !== undefined) {
            math.setAttribute('mode', 'displaystyle');
        }
        element.append(math);

        position = index + match[0].length;
    }

    element.append(document.createTextNode(value.slice(position)));
    element.dataset.renderedMathContent = value.replace(
        formulaPattern,
        (_match, inlineFormula, displayFormula) => inlineFormula ?? displayFormula ?? '',
    );
}

function renderMathContent(root = document) {
    const elements = [];
    if (root instanceof Element && root.matches('[data-math-content]')) {
        elements.push(root);
    }

    elements.push(...root.querySelectorAll('[data-math-content]'));

    elements.forEach((element) => {
        const content = element.textContent ?? '';
        if (element.dataset.renderedMathContent !== content) {
            renderMath(element, content);
        }
    });
}

function createEquationDialog() {
    const overlay = document.createElement('div');
    overlay.className = 'nets-equation-overlay';
    overlay.hidden = true;
    overlay.innerHTML = `
        <section class="nets-equation-dialog" role="dialog" aria-modal="true" aria-labelledby="nets-equation-title" tabindex="-1">
            <h2 id="nets-equation-title">Insert an equation</h2>
            <p>Build your formula visually. Use the equation keyboard for fractions, powers, roots, and symbols.</p>
            <math-field class="nets-equation-dialog__field"></math-field>
            <div class="nets-equation-dialog__templates">
                <span>Quick insert:</span>
                <button type="button" data-math-template="\\frac{}{}">Fraction</button>
                <button type="button" data-math-template="^{}">Power</button>
                <button type="button" data-math-template="\\sqrt{}">Square root</button>
            </div>
            <div class="nets-equation-dialog__actions">
                <button type="button" class="nets-equation-dialog__cancel">Cancel</button>
                <button type="button" class="nets-equation-dialog__insert">Insert equation</button>
            </div>
        </section>
    `;

    document.body.append(overlay);

    const field = overlay.querySelector('math-field');
    const insertButton = overlay.querySelector('.nets-equation-dialog__insert');
    const cancelButton = overlay.querySelector('.nets-equation-dialog__cancel');
    let target = null;
    let selectionStart = 0;
    let selectionEnd = 0;

    field.mathVirtualKeyboardPolicy = 'manual';
    window.mathVirtualKeyboard.container = document.body;

    overlay.querySelectorAll('[data-math-template]').forEach((button) => {
        button.addEventListener('click', () => {
            field.focus();
            field.executeCommand(['insert', button.dataset.mathTemplate]);
        });
    });

    function closeDialog() {
        window.mathVirtualKeyboard?.hide();
        overlay.hidden = true;
        overlay.classList.remove('has-keyboard');
        target = null;
        field.value = '';
    }

    insertButton.addEventListener('click', () => {
        const formula = field.value.trim();
        if (!formula || !target) {
            return;
        }

        const insertion = `\\(${formula}\\)`;
        target.setRangeText(insertion, selectionStart, selectionEnd, 'end');
        target.dispatchEvent(new Event('input', { bubbles: true }));
        target.focus();
        closeDialog();
    });

    cancelButton.addEventListener('click', closeDialog);
    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            closeDialog();
        }
    });
    overlay.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            closeDialog();
        }
    });

    return {
        open(input) {
            target = input;
            selectionStart = input.selectionStart ?? input.value.length;
            selectionEnd = input.selectionEnd ?? selectionStart;
            field.value = '';
            overlay.classList.add('has-keyboard');
            overlay.hidden = false;
            requestAnimationFrame(() => {
                field.focus();
                window.mathVirtualKeyboard?.show();
            });
        },
    };
}

function attachFormulaComposer(textarea, dialog) {
    const container = textarea.closest('.fi-input-wrp') ?? textarea;
    let composer = formulaComposers.get(textarea);

    if (!composer) {
        const toolbar = document.createElement('div');
        toolbar.className = 'nets-math-toolbar';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'nets-math-toolbar__button';
        button.textContent = 'Insert equation';
        const saveSelection = () => {
            textarea.dataset.mathSelectionStart = String(textarea.selectionStart ?? textarea.value.length);
            textarea.dataset.mathSelectionEnd = String(textarea.selectionEnd ?? textarea.selectionStart ?? textarea.value.length);
        };
        button.addEventListener('mousedown', saveSelection);
        button.addEventListener('focus', saveSelection);
        button.addEventListener('click', () => {
            if (textarea.dataset.mathSelectionStart !== undefined) {
                textarea.selectionStart = Number(textarea.dataset.mathSelectionStart);
                textarea.selectionEnd = Number(textarea.dataset.mathSelectionEnd);
            }
            dialog.open(textarea);
        });

        const hint = document.createElement('span');
        hint.className = 'nets-math-toolbar__hint';
        hint.textContent = 'Add fractions, powers, roots, and symbols with the visual equation editor.';
        toolbar.append(button, hint);

        const preview = document.createElement('div');
        preview.className = 'nets-math-preview';
        preview.setAttribute('aria-live', 'polite');
        preview.innerHTML = '<span class="nets-math-preview__label">Preview</span><span data-math-content></span>';
        const previewContent = preview.querySelector('[data-math-content]');

        const updatePreview = () => renderMath(previewContent, textarea.value);
        textarea.addEventListener('input', updatePreview);
        updatePreview();

        composer = { toolbar, preview };
        formulaComposers.set(textarea, composer);
    }

    if (composer.toolbar.previousElementSibling !== container) {
        container.insertAdjacentElement('afterend', composer.toolbar);
    }

    if (composer.preview.previousElementSibling !== composer.toolbar) {
        composer.toolbar.insertAdjacentElement('afterend', composer.preview);
    }
}

function getOptionLabel(index) {
    let label = '';
    let value = index + 1;

    while (value > 0) {
        const remainder = (value - 1) % 26;
        label = String.fromCharCode(65 + remainder) + label;
        value = Math.floor((value - 1) / 26);
    }

    return label;
}

function syncOptionLabels(root = document) {
    const inputs = [...root.querySelectorAll('input[data-option-label]')];

    inputs.forEach((input, index) => {
        if (!input.value.trim()) {
            input.value = getOptionLabel(index);
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });
}

function initializeMathContent() {
    const dialog = createEquationDialog();
    const scan = (root = document) => {
        const mathInputs = [];

        if (root instanceof HTMLElement && root.hasAttribute('data-math-input')) {
            mathInputs.push(root);
        }

        root.querySelectorAll?.('[data-math-input]').forEach((element) => {
            if (element instanceof HTMLInputElement || element instanceof HTMLTextAreaElement) {
                mathInputs.push(element);
            }
        });

        mathInputs.forEach((input) => attachFormulaComposer(input, dialog));
        syncOptionLabels(root);

        renderMathContent(root);
    };

    scan();

    const observer = new MutationObserver((mutations) => {
        let shouldRescan = false;

        for (const mutation of mutations) {
            if (mutation.target instanceof Element) {
                renderMathContent(mutation.target);
            }

            if (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0) {
                shouldRescan = true;
            }

            mutation.addedNodes.forEach((node) => {
                if (node instanceof HTMLElement) {
                    renderMathContent(node);
                }
            });
        }

        if (shouldRescan) {
            scan();
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });

    window.NETSMath = { render: renderMath };
    window.dispatchEvent(new CustomEvent('nets-math-ready'));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMathContent, { once: true });
} else {
    initializeMathContent();
}
