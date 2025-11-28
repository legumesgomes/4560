<x-guest-layout>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="h4 mb-2">{{ $campaign->name }}</h1>
                        @if(!empty($campaign->description))
                            <p class="text-muted">{{ $campaign->description }}</p>
                        @endif
						
						
						
						
						
   @php
                            $sessionThankYou = session()->get('leads01_public_submitted.' . $campaign->slug);
                            $flashSuccess = session('success');
                            $thankYouMessage = $sessionThankYou ?? $flashSuccess ?? '';
                            $hasSubmitted = $sessionThankYou !== null || $flashSuccess !== null;
                            $oldInput = $oldInput ?? [];
                        @endphp

                        <div id="leads01-feedback">
                            @if($hasSubmitted && $thankYouMessage !== '')
                                <div class="alert alert-success">{{ $thankYouMessage }}</div>
                            @endif
                            @if(session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="alert alert-success d-none" id="leads01-success"></div>
                            <div class="alert alert-danger d-none" id="leads01-errors">
                                <ul class="mb-0" id="leads01-errors-list"></ul>
                            </div>
                        </div>

                        @if(!$hasSubmitted)
                            <form method="POST" id="leads01-form" action="{{ route('leads01.public.submit', $campaign->slug) }}">
                                @csrf

                                @foreach($fields as $field)
                                    @php
                                        $inputName = 'field_' . $field->id;
                                        $type = $field->field_type ?? $field->type ?? 'text';

                                        $optionsRaw = $field->options ?? [];
                                        if (is_string($optionsRaw)) {
                                            $decoded = json_decode($optionsRaw, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                $optionsRaw = $decoded;
                                            } else {
                                                $optionsRaw = preg_split("/[\r\n]+/", $optionsRaw);
                                            }
                                        }
                                        $options = collect($optionsRaw)
                                            ->filter(fn($opt) => trim((string) $opt) !== '')
                                            ->map(fn($opt) => trim((string) $opt))
                                            ->values();
                                    @endphp

                                    <div class="mb-3">
                                        <label class="form-label">
                                            {{ $field->label }}
                                            @if($field->required)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>

                                        @switch($type)
                                            @case('email')
                                                <input type="email" name="{{ $inputName }}" class="form-control" value="{{ old($inputName, $oldInput[$inputName] ?? '') }}" placeholder="{{ $field->placeholder }}" @if($field->required) required @endif maxlength="150">
                                                @break
                                            @case('number')
                                                <input type="number" name="{{ $inputName }}" class="form-control" value="{{ old($inputName, $oldInput[$inputName] ?? '') }}" placeholder="{{ $field->placeholder }}" @if($field->required) required @endif>
                                                @break
                                            @case('tel')
                                                <input type="tel" name="{{ $inputName }}" class="form-control" value="{{ old($inputName, $oldInput[$inputName] ?? '') }}" placeholder="{{ $field->placeholder }}" @if($field->required) required @endif maxlength="30">
                                                @break
                                            @case('textarea')
                                                <textarea name="{{ $inputName }}" class="form-control" rows="3" placeholder="{{ $field->placeholder }}" @if($field->required) required @endif>{{ old($inputName, $oldInput[$inputName] ?? '') }}</textarea>
                                                @break
                                            @case('select')
                                                <select name="{{ $inputName }}" class="form-select" @if($field->required) required @endif>
                                                    <option value="">Selecione...</option>
                                                    @foreach($options as $option)
                                                        <option value="{{ $option }}" {{ old($inputName, $oldInput[$inputName] ?? '') == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                                @break
                                            @default
                                                <input type="text" name="{{ $inputName }}" class="form-control" value="{{ old($inputName, $oldInput[$inputName] ?? '') }}" placeholder="{{ $field->placeholder }}" @if($field->required) required @endif maxlength="255">
                                        @endswitch
                                    </div>
                                @endforeach

                                <button type="submit" class="btn btn-primary w-100" id="leads01-submit">
                                    Enviar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('leads01-form');
            if (!form || !window.fetch || !window.FormData) {
                return;
            }

            const successBox = document.getElementById('leads01-success');
            const errorsBox = document.getElementById('leads01-errors');
            const errorsList = document.getElementById('leads01-errors-list');
            const submitButton = document.getElementById('leads01-submit');

            const toggleButton = (disable, text = null) => {
                if (!submitButton) return;
                submitButton.disabled = disable;
                if (text !== null) {
                    submitButton.dataset.originalText = submitButton.dataset.originalText || submitButton.textContent;
                    submitButton.textContent = text;
                } else if (submitButton.dataset.originalText) {
                    submitButton.textContent = submitButton.dataset.originalText;
                }
            };

            const clearMessages = () => {
                if (successBox) {
                    successBox.classList.add('d-none');
                    successBox.textContent = '';
                }
                if (errorsBox && errorsList) {
                    errorsBox.classList.add('d-none');
                    errorsList.innerHTML = '';
                }
            };

            const showErrors = (errors) => {
                if (!errorsBox || !errorsList) return;
                errorsList.innerHTML = '';
                Object.values(errors).forEach((messages) => {
                    (Array.isArray(messages) ? messages : [messages]).forEach((message) => {
                        const li = document.createElement('li');
                        li.textContent = message;
                        errorsList.appendChild(li);
                    });
                });
                errorsBox.classList.remove('d-none');
            };

            const showSuccess = (message) => {
                if (!successBox) return;
                successBox.textContent = message || 'Lead enviado com sucesso!';
                successBox.classList.remove('d-none');
                form.classList.add('d-none');
            };

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
				event.stopPropagation();
                clearMessages();
                toggleButton(true, 'Enviando...');

                try {
                    const response = await fetch(form.action, {
                        method: form.method,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
						 credentials: 'same-origin',
                        body: new FormData(form),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (response.ok) {
                        showSuccess(data.message);
                        form.reset();
                        return;
                    }

                    if (response.status === 422 && data.errors) {
                        showErrors(data.errors);
                        return;
                    }

                    showErrors({ geral: [data.message || 'Não foi possível enviar o lead. Tente novamente.'] });
                } catch (error) {
                    showErrors({ conexao: ['Falha de conexão. Tente novamente.'] });
                } finally {
                    toggleButton(false);
                }
            });
        });
    </script>
</x-guest-layout>
