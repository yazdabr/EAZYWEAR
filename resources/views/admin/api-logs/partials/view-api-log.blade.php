{{-- ================= API LOG DETAIL DRAWER ================= --}}
<div
    x-data="{
        open: false,
        log: {
            request_id: '',
            method: '',
            endpoint: '',
            status: '',
            response_time: '',
            ip: '',
            date: '',
            time: ''
        },
        requestUrl: '',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'User-Agent': 'Mozilla/5.0'
        },
        requestBody: {
            example: 'Request body data'
        },
        responseBody: {},
        errorMessage: '',

        openDrawer(event) {
            this.log = event.detail;
            this.requestUrl = window.location.origin + this.log.endpoint;
            this.open = true;

            if (this.log.status >= 400) {
                this.errorMessage = this.log.status >= 500
                    ? 'Internal server error occurred while processing the request.'
                    : 'The request could not be processed because the submitted data was invalid.';
            } else {
                this.errorMessage = '';
            }

            this.responseBody = {
                status: this.log.status,
                message: this.log.status >= 400
                    ? 'Request failed'
                    : 'Request processed successfully'
            };
        },

        closeDrawer() {
            this.open = false;
        },

        statusType() {
            if (this.log.status >= 200 && this.log.status < 300) {
                return 'success';
            }
            if (this.log.status >= 400 && this.log.status < 500) {
                return 'client-error';
            }
            if (this.log.status >= 500) {
                return 'server-error';
            }
            return 'other';
        },

        statusLabel() {
            if (this.statusType() === 'success') {
                return 'Success';
            }
            if (this.statusType() === 'client-error') {
                return 'Client Error';
            }
            if (this.statusType() === 'server-error') {
                return 'Server Error';
            }
            return 'Redirect';
        },

        copyText(text) {
            navigator.clipboard.writeText(text);
            this.$dispatch('toast', {
                type: 'success',
                title: 'Copied',
                message: 'Data copied to clipboard.'
            });
        }
    }"
    @open-api-log.window="openDrawer($event)"
    x-show="open"
    class="relative z-[100]"
    style="display:none;">

    {{-- ================= BACKDROP ================= --}}
    <div
        x-show="open"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeDrawer()"
        class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm">
    </div>

    {{-- ================= DRAWER ================= --}}
    <div
        x-show="open"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 flex w-full max-w-2xl flex-col bg-white shadow-2xl">

        {{-- ================= HEADER ================= --}}
        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-6 py-5">
            <div class="flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#AE7C18]/10">
                    <x-heroicon-o-command-line class="h-6 w-6 text-[#AE7C18]" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        API Log Detail
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        <span x-text="log.request_id"></span>
                    </p>
                </div>
            </div>

            {{-- Close --}}
            <button
                type="button"
                @click="closeDrawer()"
                class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                <x-heroicon-o-x-mark class="h-6 w-6" />
            </button>
        </div>

        {{-- ================= CONTENT ================= --}}
        <div class="flex-1 overflow-y-auto">
            <div class="space-y-6 p-6">

                {{-- ================= STATUS ================= --}}
                <div
                    class="rounded-2xl border p-5"
                    :class="{
                        'border-emerald-200 bg-emerald-50': statusType() === 'success',
                        'border-orange-200 bg-orange-50': statusType() === 'client-error',
                        'border-red-200 bg-red-50': statusType() === 'server-error',
                        'border-amber-200 bg-amber-50': statusType() === 'other'
                    }">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            {{-- Success --}}
                            <template x-if="statusType() === 'success'">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100">
                                    <x-heroicon-o-check-circle class="h-6 w-6 text-emerald-600" />
                                </div>
                            </template>

                            {{-- Client Error --}}
                            <template x-if="statusType() === 'client-error'">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100">
                                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-orange-600" />
                                </div>
                            </template>

                            {{-- Server Error --}}
                            <template x-if="statusType() === 'server-error'">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100">
                                    <x-heroicon-o-x-circle class="h-6 w-6 text-red-600" />
                                </div>
                            </template>

                            <div>
                                <p
                                    class="text-sm font-semibold"
                                    :class="{
                                        'text-emerald-700': statusType() === 'success',
                                        'text-orange-700': statusType() === 'client-error',
                                        'text-red-700': statusType() === 'server-error',
                                        'text-amber-700': statusType() === 'other'
                                    }"
                                    x-text="statusLabel()">
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    HTTP Status Code
                                </p>
                            </div>
                        </div>

                        <span
                            class="text-2xl font-bold"
                            :class="{
                                'text-emerald-700': statusType() === 'success',
                                'text-orange-700': statusType() === 'client-error',
                                'text-red-700': statusType() === 'server-error',
                                'text-amber-700': statusType() === 'other'
                            }"
                            x-text="log.status">
                        </span>
                    </div>
                </div>

                {{-- ================= REQUEST INFORMATION ================= --}}
                <div class="rounded-2xl border border-slate-200 bg-white">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h3 class="font-semibold text-slate-900">
                            Request Information
                        </h3>
                    </div>

                    <div class="grid gap-5 p-5 sm:grid-cols-2">
                        {{-- Request ID --}}
                        <div>
                            <p class="text-xs font-medium text-slate-500">
                                Request ID
                            </p>
                            <p
                                class="mt-1.5 break-all text-sm font-semibold text-slate-900"
                                x-text="log.request_id">
                            </p>
                        </div>

                        {{-- Method --}}
                        <div>
                            <p class="text-xs font-medium text-slate-500">
                                Method
                            </p>
                            <span
                                class="mt-1.5 inline-flex rounded-lg bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700"
                                x-text="log.method">
                            </span>
                        </div>

                        {{-- Endpoint --}}
                        <div class="sm:col-span-2">
                            <p class="text-xs font-medium text-slate-500">
                                Endpoint
                            </p>
                            <code
                                class="mt-1.5 block break-all rounded-xl bg-slate-100 px-3 py-2 text-xs font-medium text-slate-700"
                                x-text="log.endpoint">
                            </code>
                        </div>

                        {{-- URL --}}
                        <div class="sm:col-span-2">
                            <p class="text-xs font-medium text-slate-500">
                                Request URL
                            </p>
                            <div class="mt-1.5 flex items-center gap-2">
                                <code
                                    class="min-w-0 flex-1 break-all rounded-xl bg-slate-100 px-3 py-2 text-xs text-slate-700"
                                    x-text="requestUrl">
                                </code>
                                <button
                                    type="button"
                                    @click="copyText(requestUrl)"
                                    class="shrink-0 rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                                    <x-heroicon-o-clipboard-document class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        {{-- Response Time --}}
                        <div>
                            <p class="text-xs font-medium text-slate-500">
                                Response Time
                            </p>
                            <p
                                class="mt-1.5 text-sm font-semibold text-slate-900"
                                x-text="log.response_time">
                            </p>
                        </div>

                        {{-- IP --}}
                        <div>
                            <p class="text-xs font-medium text-slate-500">
                                IP Address
                            </p>
                            <code
                                class="mt-1.5 block text-xs text-slate-700"
                                x-text="log.ip">
                            </code>
                        </div>

                        {{-- Date --}}
                        <div>
                            <p class="text-xs font-medium text-slate-500">
                                Date
                            </p>
                            <p
                                class="mt-1.5 text-sm font-medium text-slate-700"
                                x-text="log.date">
                            </p>
                        </div>

                        {{-- Time --}}
                        <div>
                            <p class="text-xs font-medium text-slate-500">
                                Time
                            </p>
                            <p
                                class="mt-1.5 text-sm font-medium text-slate-700"
                                x-text="log.time">
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ================= ERROR MESSAGE ================= --}}
                <template x-if="errorMessage">
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
                        <div class="flex gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-100">
                                <x-heroicon-o-exclamation-circle class="h-5 w-5 text-red-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-red-800">
                                    Error Message
                                </h3>
                                <p
                                    class="mt-1 text-sm leading-6 text-red-700"
                                    x-text="errorMessage">
                                </p>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- ================= REQUEST HEADERS ================= --}}
                <div class="rounded-2xl border border-slate-200 bg-white">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <h3 class="font-semibold text-slate-900">
                            Request Headers
                        </h3>
                        <button
                            type="button"
                            @click="copyText(JSON.stringify(headers, null, 2))"
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                            <x-heroicon-o-clipboard-document class="h-4 w-4" />
                            Copy
                        </button>
                    </div>
                    <div class="p-5">
                        <pre
                            class="overflow-x-auto rounded-xl bg-slate-900 p-4 text-xs leading-6 text-slate-200"
                            x-text="JSON.stringify(headers, null, 2)">
                        </pre>
                    </div>
                </div>

                {{-- ================= REQUEST BODY ================= --}}
                <div class="rounded-2xl border border-slate-200 bg-white">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <h3 class="font-semibold text-slate-900">
                            Request Body
                        </h3>
                        <button
                            type="button"
                            @click="copyText(JSON.stringify(requestBody, null, 2))"
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                            <x-heroicon-o-clipboard-document class="h-4 w-4" />
                            Copy
                        </button>
                    </div>
                    <div class="p-5">
                        <pre
                            class="overflow-x-auto rounded-xl bg-slate-900 p-4 text-xs leading-6 text-slate-200"
                            x-text="JSON.stringify(requestBody, null, 2)">
                        </pre>
                    </div>
                </div>

                {{-- ================= RESPONSE ================= --}}
                <div class="rounded-2xl border border-slate-200 bg-white">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <h3 class="font-semibold text-slate-900">
                            Response Body
                        </h3>
                        <button
                            type="button"
                            @click="copyText(JSON.stringify(responseBody, null, 2))"
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                            <x-heroicon-o-clipboard-document class="h-4 w-4" />
                            Copy
                        </button>
                    </div>
                    <div class="p-5">
                        <pre
                            class="overflow-x-auto rounded-xl bg-slate-900 p-4 text-xs leading-6 text-slate-200"
                            x-text="JSON.stringify(responseBody, null, 2)">
                        </pre>
                    </div>
                </div>

                {{-- ================= METADATA ================= --}}
                <div class="rounded-2xl border border-slate-200 bg-white">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h3 class="font-semibold text-slate-900">
                            Metadata
                        </h3>
                    </div>
                    <div class="grid gap-4 p-5 sm:grid-cols-2">
                        <div>
                            <p class="text-xs text-slate-500">
                                Source
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-700">
                                Admin API
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">
                                Environment
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-700">
                                Local
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">
                                Authentication
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-700">
                                Bearer Token
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">
                                API Version
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-700">
                                v1
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ================= FOOTER ================= --}}
        <div class="flex shrink-0 items-center justify-end border-t border-slate-200 bg-white px-6 py-4">
            <button
                type="button"
                @click="closeDrawer()"
                class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Close
            </button>
        </div>

    </div>
</div>