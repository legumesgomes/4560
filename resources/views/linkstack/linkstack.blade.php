@extends('linkstack.layout')

@section('content')


 @php
        // Normaliza flags para facilitar o uso no Blade
        $hasCatalog = $catalogEnabled ?? false;
        // Se tiver variável $leadsEnabled vinda do controller, respeita.
        // Caso contrário, considera que existe leads se $leadCampaign não estiver vazio.
        $hasLeads   = isset($leadsEnabled)
            ? ($leadsEnabled && !empty($leadCampaign))
            : (!empty($leadCampaign));
    @endphp



    @push('linkstack-content')
        @if($hasCatalog || $hasLeads)
            <div class="ls-tab-card ls-tab-card--solid">
                <div class="ls-tab-header">
                    <button class="ls-tab-button active"
                            data-ls-tab-target="ls-tab-profile"
                            aria-selected="true">
                        Perfil
                    </button>

                    @if($hasCatalog)
                        <button class="ls-tab-button"
                                data-ls-tab-target="ls-tab-catalog"
                                aria-selected="false">
                            Catálogo
                        </button>
                    @endif

                    @if($hasLeads)
                        <button class="ls-tab-button"
                                data-ls-tab-target="ls-tab-leads"
                                aria-selected="false">
                            Leads
                        </button>
                    @endif
                </div>

                <div class="mt-3">
                    {{-- CONTEÚDO: PERFIL --}}
                    <div id="ls-tab-profile" class="ls-tab-pane active">
                        @include('linkstack.elements.buttons')
                        @yield('content')
                        @include('linkstack.modules.footer')
                    </div>

                    {{-- CONTEÚDO: CATÁLOGO (lazy load via fetch) --}}
                    @if($hasCatalog)
                        <div id="ls-tab-catalog" class="ls-tab-pane" data-loaded="false">
                            <div class="text-center text-muted py-4" id="ls-catalog-placeholder">
                                Clique na aba Catálogo para carregar os produtos.
                            </div>
                        </div>
                    @endif

                    {{-- CONTEÚDO: LEADS --}}
                    @if($hasLeads)
                        <div id="ls-tab-leads" class="ls-tab-pane">
                            <div class="py-3">
                                <h2 class="h5 mb-3">{{ $leadCampaign->name }}</h2>

                                @if($leadCampaign->description)
                                    <p class="text-muted">{{ $leadCampaign->description }}</p>
                                @endif

                                @if(session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @elseif(session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
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

                                @if(!session('success'))
                                {{-- IMPORTANTE: usa rota nomeada do plugin --}}
                                <form method="POST"
                                      action="{{ route('leads01.public.submit', $leadCampaign->slug) }}">
                                    @csrf

                                    @foreach($leadCampaign->fields as $field)
                                        @php
                                            $fieldId   = 'field_'.$field->id;
                                            $label     = $field->label ?? $field->name;
                                            $required  = (bool) ($field->required ?? false);
                                            $type      = $field->type ?? 'text';
                                            $oldValue  = old($fieldId);

                                            $optionsRaw = $field->options ?? [];
                                            if (is_string($optionsRaw)) {
                                                $options = json_decode($optionsRaw, true) ?? [];
                                            } elseif (is_array($optionsRaw)) {
                                                $options = $optionsRaw;
                                            } else {
                                                $options = [];
                                            }
                                        @endphp

                                        <div class="mb-3">
                                            <label for="{{ $fieldId }}" class="form-label">
                                                {{ $label }} @if($required) * @endif
                                            </label>

                                            @if($type === 'textarea')
                                                <textarea
                                                    id="{{ $fieldId }}"
                                                    name="{{ $fieldId }}"
                                                    class="form-control"
                                                    rows="3"
                                                    @if($required) required @endif>{{ $oldValue }}</textarea>
                                            @elseif($type === 'select')
                                                <select
                                                    id="{{ $fieldId }}"
                                                    name="{{ $fieldId }}"
                                                    class="form-select"
                                                    @if($required) required @endif>
                                                    <option value="">Selecione...</option>
                                                    @foreach($options as $opt)
                                                        <option value="{{ $opt }}" @if($oldValue === $opt) selected @endif>
                                                            {{ $opt }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                @php
                                                    $htmlType = in_array($type, ['email', 'tel', 'number', 'text'], true)
                                                        ? $type
                                                        : 'text';
                                                @endphp
                                                <input
                                                    type="{{ $htmlType }}"
                                                    id="{{ $fieldId }}"
                                                    name="{{ $fieldId }}"
                                                    class="form-control"
                                                    value="{{ $oldValue }}"
                                                    @if($required) required @endif>
                                            @endif
                                        </div>
                                    @endforeach

                                    <button type="submit" class="btn btn-primary w-100">
                                        Enviar
                                    </button>
                                </form>
                                @endif

                                @if($leadCampaign->thank_you_message)
                                    <p class="text-muted small mt-2">
                                        Após o envio, o usuário verá a mensagem:
                                        “{{ $leadCampaign->thank_you_message }}”
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            {{-- Comportamento original: sem catálogo/sem leads, só os botões padrão --}}
            @include('linkstack.elements.buttons')
            @yield('content')
            @include('linkstack.modules.footer')
        @endif
    @endpush

    @php
        $hasCatalog = $catalogEnabled ?? false;
        $hasLeads   = isset($leadsEnabled)
            ? ($leadsEnabled && !empty($leadCampaign))
            : (!empty($leadCampaign));
    @endphp

    @if($hasCatalog || $hasLeads)
        @push('linkstack-body-end')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const buttons = document.querySelectorAll('[data-ls-tab-target]');
                    const panes = {};
                    ['ls-tab-profile', 'ls-tab-catalog', 'ls-tab-leads'].forEach(function (id) {
                        const el = document.getElementById(id);
                        if (el) {
                            panes[id] = el;
                        }
                    });

                    const tabCard = document.querySelector('.ls-tab-card');
                    let catalogLoaded = false;

                    function updateTabCardBackground(targetId) {
                        if (!tabCard) return;

                        const shouldBeSolid = ['ls-tab-catalog', 'ls-tab-leads'].includes(targetId);

                        tabCard.classList.toggle('ls-tab-card--solid', shouldBeSolid);
                    }

                    function setActive(targetId) {
                        Object.values(panes).forEach(function (pane) {
                            pane.classList.remove('active');
                        });

                        buttons.forEach(function (btn) {
                            btn.classList.remove('active');
                            btn.setAttribute('aria-selected', 'false');
                        });

                        const targetPane = document.getElementById(targetId);
                        if (!targetPane) return;

                        targetPane.classList.add('active');

                        const activeButton = Array.from(buttons).find(function (btn) {
                            return btn.dataset.lsTabTarget === targetId;
                        });
                        if (activeButton) {
                            activeButton.classList.add('active');
                            activeButton.setAttribute('aria-selected', 'true');
                        }

                        updateTabCardBackground(targetId);

                        if (targetId === 'ls-tab-catalog' && !catalogLoaded) {
                            const placeholder = document.getElementById('ls-catalog-placeholder');
                            fetch("{{ route('products.catalog', ['username' => $publicProfile->name]) }}")
                                .then(resp => resp.text())
                                .then(html => {
                                    const pane = document.getElementById('ls-tab-catalog');
                                    if (pane) {
                                        pane.innerHTML = html;
                                        catalogLoaded = true;
                                    }
                                })
                                .catch(() => {
                                    if (placeholder) {
                                        placeholder.textContent = 'Não foi possível carregar o catálogo.';
                                    }
                                });
                        }
                    }

                    buttons.forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            const targetId = btn.dataset.lsTabTarget;
                            setActive(targetId);
                        });
                    });

                    updateTabCardBackground('ls-tab-profile');
                });
            </script>
        @endpush
    @endif
@endsection
