import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
    static targets: string[];
    static values: {
        components: {
            type: ArrayConstructor;
            default: string[];
        };
        plugins: {
            type: ArrayConstructor;
            default: string[];
        };
        extraOptions: {
            type: ObjectConstructor;
            default: {};
        };
        cdnUrl: {
            type: StringConstructor;
            default: string;
        };
    };
    readonly inputTarget: HTMLInputElement;
    readonly editorContainerTarget: HTMLElement;
    componentsValue: string[];
    pluginsValue: string[];
    extraOptionsValue: Record<string, any>;
    cdnUrlValue: string;
    connect(): Promise<void>;
    disconnect(): void;
}
