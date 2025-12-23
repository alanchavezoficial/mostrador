/**
 * Rich Text Editor
 * Editor de texto enriquecido con soporte para múltiples formatos
 */

class RichEditor {
    constructor(elementId, options = {}) {
        this.container = document.getElementById(elementId);
        this.options = {
            placeholderText: options.placeholderText || 'Escribe aquí...',
            onContentChange: options.onContentChange || null,
            ...options
        };

        this.init();
    }

    init() {
        // Crear estructura del editor
        this.createToolbar();
        this.createEditor();
        this.attachEvents();
    }

    createToolbar() {
        const toolbar = document.createElement('div');
        toolbar.className = 'rich-editor-toolbar';

        const buttons = [
            // Formatos de texto
            { cmd: 'bold', icon: '𝐁', title: 'Negrita (Ctrl+B)', class: 'format-bold' },
            { cmd: 'italic', icon: '𝘐', title: 'Itálica (Ctrl+I)', class: 'format-italic' },
            { cmd: 'underline', icon: '𝐔', title: 'Subrayado (Ctrl+U)', class: 'format-underline' },
            { cmd: 'strikethrough', icon: '𝐒', title: 'Tachado', class: 'format-strikethrough' },
            
            // Separador
            { separator: true },

            // Tamaño de fuente
            { type: 'select', name: 'fontSize', title: 'Tamaño de fuente', options: [
                { value: '', text: 'Tamaño' },
                { value: '10px', text: '10px' },
                { value: '14px', text: '14px' },
                { value: '16px', text: '16px' },
                { value: '18px', text: '18px' },
                { value: '24px', text: '24px' },
                { value: '32px', text: '32px' }
            ]},

            // Colores
            { type: 'colorpicker', name: 'textColor', title: 'Color de texto' },

            // Separador
            { separator: true },

            // Alineación
            { cmd: 'justifyLeft', icon: '⬅', title: 'Alinear a izquierda', class: 'align-left' },
            { cmd: 'justifyCenter', icon: '⬆', title: 'Centrar', class: 'align-center' },
            { cmd: 'justifyRight', icon: '➡', title: 'Alinear a derecha', class: 'align-right' },
            { cmd: 'justifyFull', icon: '⧉', title: 'Justificar', class: 'align-justify' },

            // Separador
            { separator: true },

            // Listas
            { cmd: 'insertUnorderedList', icon: '◦', title: 'Lista con puntos', class: 'list-bullet' },
            { cmd: 'insertOrderedList', icon: '1.', title: 'Lista numerada', class: 'list-number' },
            { cmd: 'indent', icon: '→', title: 'Aumentar sangría', class: 'indent' },
            { cmd: 'outdent', icon: '←', title: 'Disminuir sangría', class: 'outdent' },

            // Separador
            { separator: true },

            // Otros formatos
            { cmd: 'formatBlock', value: '<h2>', text: 'Encabezado 2', title: 'Encabezado 2', class: 'heading' },
            { cmd: 'formatBlock', value: '<h3>', text: 'Encabezado 3', title: 'Encabezado 3', class: 'heading' },
            { cmd: 'formatBlock', value: '<p>', text: 'Párrafo', title: 'Párrafo normal', class: 'paragraph' },
            { cmd: 'formatBlock', value: '<blockquote>', text: 'Cita', title: 'Cita', class: 'quote' },

            // Separador
            { separator: true },

            // Enlaces e imágenes
            { type: 'button', text: '🔗', title: 'Insertar enlace', class: 'insert-link' },
            { type: 'button', text: '🖼', title: 'Insertar imagen', class: 'insert-image' },

            // Separador
            { separator: true },

            // Limpiar formato
            { cmd: 'removeFormat', text: 'Limpiar', title: 'Limpiar formato', class: 'clear-format' },
        ];

        buttons.forEach(btn => {
            if (btn.separator) {
                const sep = document.createElement('div');
                sep.className = 'toolbar-separator';
                toolbar.appendChild(sep);
            } else if (btn.type === 'select') {
                const select = document.createElement('select');
                select.className = `toolbar-select ${btn.name}`;
                select.title = btn.title;
                
                btn.options.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.text;
                    select.appendChild(option);
                });

                select.addEventListener('change', (e) => {
                    if (e.target.value) {
                        document.execCommand(btn.name, false, e.target.value);
                        e.target.value = '';
                    }
                });

                toolbar.appendChild(select);
            } else if (btn.type === 'colorpicker') {
                const colorContainer = document.createElement('div');
                colorContainer.className = 'toolbar-color-container';
                colorContainer.title = btn.title;

                const colorPreview = document.createElement('div');
                colorPreview.className = 'toolbar-color-preview';
                colorPreview.style.backgroundColor = '#000000';

                const colorInput = document.createElement('input');
                colorInput.type = 'color';
                colorInput.className = 'toolbar-color-input';
                colorInput.value = '#000000';

                // Hacer que el preview abra el picker
                colorPreview.addEventListener('click', () => {
                    colorInput.click();
                });

                colorInput.addEventListener('change', (e) => {
                    colorPreview.style.backgroundColor = e.target.value;
                    document.execCommand('foreColor', false, e.target.value);
                    this.editor.focus();
                });

                colorInput.addEventListener('input', (e) => {
                    colorPreview.style.backgroundColor = e.target.value;
                });

                colorContainer.appendChild(colorPreview);
                colorContainer.appendChild(colorInput);
                toolbar.appendChild(colorContainer);
            } else if (btn.type === 'button' && btn.class === 'insert-link') {
                const linkBtn = document.createElement('button');
                linkBtn.type = 'button';
                linkBtn.textContent = btn.text;
                linkBtn.title = btn.title;
                linkBtn.className = btn.class;

                linkBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = prompt('Ingresa la URL:');
                    if (url) {
                        document.execCommand('createLink', false, url);
                    }
                });

                toolbar.appendChild(linkBtn);
            } else if (btn.type === 'button' && btn.class === 'insert-image') {
                const imageBtn = document.createElement('button');
                imageBtn.type = 'button';
                imageBtn.textContent = btn.text;
                imageBtn.title = btn.title;
                imageBtn.className = btn.class;

                imageBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = prompt('Ingresa la URL de la imagen:');
                    if (url) {
                        document.execCommand('insertImage', false, url);
                    }
                });

                toolbar.appendChild(imageBtn);
            } else {
                const button = document.createElement('button');
                button.type = 'button';
                button.innerHTML = btn.text || btn.icon;
                button.title = btn.title;
                button.className = `toolbar-btn ${btn.class || ''}`;

                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (btn.value) {
                        document.execCommand(btn.cmd, false, btn.value);
                    } else {
                        document.execCommand(btn.cmd, false, null);
                    }
                    this.editor.focus();
                });

                toolbar.appendChild(button);
            }
        });

        this.container.appendChild(toolbar);
        this.toolbar = toolbar;
    }

    createEditor() {
        const editorWrapper = document.createElement('div');
        editorWrapper.className = 'rich-editor-wrapper';

        const editor = document.createElement('div');
        editor.className = 'rich-editor-content';
        editor.contentEditable = 'true';
        editor.setAttribute('data-placeholder', this.options.placeholderText);

        editorWrapper.appendChild(editor);
        this.container.appendChild(editorWrapper);
        this.editor = editor;
    }

    attachEvents() {
        // Evitar que se paste HTML malicioso
        this.editor.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = e.clipboardData.getData('text/plain');
            document.execCommand('insertText', false, text);
        });

        // Notificar cambios
        this.editor.addEventListener('input', () => {
            if (this.options.onContentChange) {
                this.options.onContentChange(this.getContent());
            }
        });

        // Actualizar input oculto cuando hay cambios
        const hiddenInput = this.container.querySelector('input[type="hidden"]');
        if (hiddenInput) {
            this.editor.addEventListener('input', () => {
                hiddenInput.value = this.getContent();
            });
        }
    }

    getContent() {
        return this.editor.innerHTML;
    }

    setContent(html) {
        this.editor.innerHTML = html;
    }

    clear() {
        this.editor.innerHTML = '';
    }

    getPlainText() {
        return this.editor.innerText;
    }

    focus() {
        this.editor.focus();
    }

    // Método estático para inicializar múltiples editores
    static initAll(className = 'rich-editor') {
        const containers = document.querySelectorAll(`.${className}`);
        containers.forEach(container => {
            new RichEditor(container.id);
        });
    }
}

// Nota: La inicialización automática se deshabilita para evitar
// duplicados cuando las vistas inicializan explícitamente el editor.
