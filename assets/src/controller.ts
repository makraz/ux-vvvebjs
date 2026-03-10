import { Controller } from '@hotwired/stimulus';

declare const Vvveb: any;

// Maps component group names to their VvvebJs script files
const COMPONENT_SCRIPTS: Record<string, string> = {
    'common': 'libs/builder/components-common.js',
    'html': 'libs/builder/components-html.js',
    'elements': 'libs/builder/components-elements.js',
    'bootstrap5': 'libs/builder/components-bootstrap5.js',
    'widgets': 'libs/builder/components-widgets.js',
    'embeds': 'libs/builder/components-embeds.js',
};

// Maps plugin names to their VvvebJs script files
const PLUGIN_SCRIPTS: Record<string, string[]> = {
    'google-fonts': ['libs/builder/plugin-google-fonts.js'],
    'codemirror': [
        'libs/codemirror/lib/codemirror.js',
        'libs/codemirror/lib/xml.js',
        'libs/codemirror/lib/formatting.js',
        'libs/builder/plugin-codemirror.js',
    ],
    'jszip': [
        'libs/jszip/jszip.min.js',
        'libs/jszip/filesaver.min.js',
        'libs/builder/plugin-jszip.js',
    ],
    'aos': ['libs/builder/plugin-aos.js'],
    'ai-assistant': ['libs/builder/plugin-ai-assistant.js'],
    'media': [
        'libs/media/media.js',
        'libs/builder/plugin-media.js',
    ],
};

// Plugin-related CSS files
const PLUGIN_CSS: Record<string, string[]> = {
    'codemirror': [
        'libs/codemirror/lib/codemirror.css',
        'libs/codemirror/theme/material.css',
    ],
    'media': ['libs/media/media.css'],
};

export default class extends Controller {
    static targets = ['input', 'editorContainer'];
    static values = {
        components: { type: Array, default: ['common', 'html', 'elements', 'bootstrap5'] },
        plugins: { type: Array, default: ['codemirror'] },
        extraOptions: { type: Object, default: {} },
        cdnUrl: { type: String, default: 'https://cdn.jsdelivr.net/gh/givanz/VvvebJs@master' },
    };

    declare readonly inputTarget: HTMLInputElement;
    declare readonly editorContainerTarget: HTMLElement;
    declare componentsValue: string[];
    declare pluginsValue: string[];
    declare extraOptionsValue: Record<string, any>;
    declare cdnUrlValue: string;

    private builder: any = null;
    private editorId: string = '';

    async connect(): Promise<void> {
        this.editorId = `vvvebjs-editor-${Math.random().toString(36).slice(2, 10)}`;

        try {
            await this.loadEditorCSS();
            await this.loadCoreScripts();
            await this.loadComponentScripts();
            await this.loadPluginAssets();
            this.buildEditorDOM();
            this.initializeBuilder();
        } catch (error) {
            console.error('[ux-vvvebjs] Failed to initialize VvvebJs:', error);
        }
    }

    disconnect(): void {
        this.editorContainerTarget.innerHTML = '';
        this.builder = null;
    }

    private async loadEditorCSS(): Promise<void> {
        const cssFiles = [
            `${this.cdnUrlValue}/css/editor.css`,
        ];

        // Load plugin CSS
        for (const plugin of this.pluginsValue) {
            const files = PLUGIN_CSS[plugin];
            if (files) {
                for (const file of files) {
                    cssFiles.push(`${this.cdnUrlValue}/${file}`);
                }
            }
        }

        const promises = cssFiles.map((href) => this.loadCSS(href));
        await Promise.all(promises);
    }

    private async loadCoreScripts(): Promise<void> {
        const coreScripts = [
            'libs/builder/builder.js',
            'libs/builder/undo.js',
            'libs/builder/inputs.js',
            'libs/builder/section.js',
        ];

        for (const script of coreScripts) {
            await this.loadScript(`${this.cdnUrlValue}/${script}`);
        }
    }

    private async loadComponentScripts(): Promise<void> {
        for (const group of this.componentsValue) {
            const scriptFile = COMPONENT_SCRIPTS[group];
            if (scriptFile) {
                await this.loadScript(`${this.cdnUrlValue}/${scriptFile}`);
            }
        }
    }

    private async loadPluginAssets(): Promise<void> {
        for (const plugin of this.pluginsValue) {
            const scripts = PLUGIN_SCRIPTS[plugin];
            if (scripts) {
                for (const script of scripts) {
                    await this.loadScript(`${this.cdnUrlValue}/${script}`);
                }
            }
        }
    }

    private buildEditorDOM(): void {
        const opts = this.extraOptionsValue;
        const height = typeof opts.height === 'number' ? `${opts.height}px` : (opts.height || '600px');
        const borderStyle = opts.border === true
            ? '1px solid #dee2e6'
            : (typeof opts.border === 'string' ? opts.border : 'none');

        this.editorContainerTarget.innerHTML = `
            <div id="${this.editorId}" class="vvvebjs-editor-wrapper" style="height: ${height}; border: ${borderStyle}; overflow: hidden; position: relative;">
                <div id="${this.editorId}-builder" style="width: 100%; height: 100%;">
                    <iframe id="${this.editorId}-iframe" src="about:blank" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>
        `;
    }

    private initializeBuilder(): void {
        if (typeof Vvveb === 'undefined') {
            console.error('[ux-vvvebjs] Vvveb global not found. Scripts may have failed to load.');
            return;
        }

        const opts = this.extraOptionsValue;
        const initialHtml = this.inputTarget.value || '<html><head></head><body><div class="container mt-4"><h1>Start editing...</h1><p>Drag components from the sidebar to build your page.</p></div></body></html>';

        Vvveb.Builder.init('about:blank', () => {
            Vvveb.Builder.setHtml(initialHtml);

            if (opts.designerMode) {
                Vvveb.Builder.designerMode = true;
            }

            if (opts.readOnly) {
                Vvveb.Builder.isPreview = true;
            }

            if (opts.uploadUrl) {
                Vvveb.MediaModal = Vvveb.MediaModal || {};
                Vvveb.MediaModal.uploadUrl = opts.uploadUrl;
            }
        });

        Vvveb.Gui.init();

        this.builder = Vvveb.Builder;

        // Sync HTML on relevant events
        document.addEventListener('vvveb.iframe.loaded', () => this.syncData());

        // Periodically sync data (VvvebJs doesn't have a reliable change event for all mutations)
        this.setupAutoSync();
    }

    private setupAutoSync(): void {
        // Observe the form for submit events to ensure data is synced
        const form = this.inputTarget.closest('form');
        if (form) {
            form.addEventListener('submit', () => this.syncData());
        }

        // Also sync before the page unloads
        window.addEventListener('beforeunload', () => this.syncData());
    }

    private syncData(): void {
        if (!this.builder) return;

        try {
            const html = Vvveb.Builder.getHtml(false);
            if (html && html !== this.inputTarget.value) {
                this.inputTarget.value = html;
                this.inputTarget.dispatchEvent(new Event('input', { bubbles: true }));
                this.inputTarget.dispatchEvent(new Event('change', { bubbles: true }));
            }
        } catch {
            // Builder may not be ready yet
        }
    }

    private loadScript(src: string): Promise<void> {
        return new Promise((resolve, reject) => {
            // Skip if already loaded
            if (document.querySelector(`script[src="${src}"]`)) {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.onload = () => resolve();
            script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
            document.head.appendChild(script);
        });
    }

    private loadCSS(href: string): Promise<void> {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`link[href="${href}"]`)) {
                resolve();
                return;
            }

            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.onload = () => resolve();
            link.onerror = () => reject(new Error(`Failed to load CSS: ${href}`));
            document.head.appendChild(link);
        });
    }
}
