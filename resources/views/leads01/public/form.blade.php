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

                         @if(!$hasSubmitted)
                            <form method="POST" action="{{ route('leads01.public.submit', $campaign->slug) }}">
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

                                <button type="submit" class="btn btn-primary w-100">
                                    Enviar
                                </button>
                            </form>
                        @endif
						
						
						
						
						
                             
						
						
						
						
						
						
						
						
						
						
						
						
						
						
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
