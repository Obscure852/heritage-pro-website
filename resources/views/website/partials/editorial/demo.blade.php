@php
    $formErrors = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $editions = ['Heritage Pro — Junior Schools', 'Heritage Pro — Senior Schools', 'Heritage Pro — Colleges & Institutes', 'Not sure yet'];
    $learnerBands = ['Under 200', '200 – 500', '500 – 1,500', '1,500 – 5,000', '5,000+'];
    $phone = collect($site['contact']['details'])->firstWhere('icon', 'phone')['value'] ?? '';
@endphp
<section id="demo" class="hp-demo hp-band hp-band--navy">
    {{-- The book-demo redirect anchors on #contact; keep that target alive. --}}
    <span id="contact"></span>
    <div class="hp-demo__inner">
        <div>
            <p class="hp-eyebrow hp-eyebrow--onnavy">XI. Next step</p>
            <h2 class="hp-demo__title">Book a 30-minute demo.</h2>
            <p class="hp-lead hp-lead--onnavy hp-demo__lead">We will walk your own term structure, grading rules and fee model on screen — not a generic tour. Bring your registrar and your bursar.</p>
            <div class="hp-ruled">
                <div>A working session with an implementation lead</div>
                <div>Your report card format demonstrated live</div>
                <div>A written quote within two business days</div>
            </div>
            @if ($phone !== '')
                <p class="hp-demo__phone">Prefer to talk? <a href="tel:{{ str_replace(' ', '', $phone) }}">{{ $phone }}</a></p>
            @endif
        </div>

        <form class="hp-form" action="{{ route('website.book-demo') }}" method="POST">
            @csrf
            <p class="hp-form__title">Request a demonstration</p>

            @if (session('book_demo_success'))
                <p class="hp-alert" role="status">{{ session('book_demo_success') }}</p>
            @endif
            @if (session('book_demo_error'))
                <p class="hp-alert hp-alert--error" role="alert">{{ session('book_demo_error') }}</p>
            @endif

            <div class="hp-form__grid">
                <div class="hp-field">
                    <label for="demo-full-name">Full name</label>
                    <input type="text" id="demo-full-name" name="full_name" placeholder="Your name" value="{{ old('full_name') }}" @class(['is-invalid' => $formErrors->has('full_name')])>
                    @error('full_name')
                        <p class="hp-field__error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="hp-field">
                    <label for="demo-role">Role</label>
                    <input type="text" id="demo-role" name="role" placeholder="Head, registrar, bursar…" value="{{ old('role') }}" @class(['is-invalid' => $formErrors->has('role')])>
                    @error('role')
                        <p class="hp-field__error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="hp-field">
                    <label for="demo-institution">Institution</label>
                    <input type="text" id="demo-institution" name="institution" placeholder="School or college name" value="{{ old('institution') }}" @class(['is-invalid' => $formErrors->has('institution')])>
                    @error('institution')
                        <p class="hp-field__error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="hp-field">
                    <label for="demo-learners">Learners</label>
                    <select id="demo-learners" name="learner_band" @class(['is-invalid' => $formErrors->has('learner_band')])>
                        @foreach ($learnerBands as $band)
                            <option value="{{ $band }}" @selected(old('learner_band') === $band)>{{ $band }}</option>
                        @endforeach
                    </select>
                    @error('learner_band')
                        <p class="hp-field__error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="hp-field">
                    <label for="demo-email">Work email</label>
                    <input type="email" id="demo-email" name="work_email" placeholder="name@school.ac.bw" value="{{ old('work_email') }}" @class(['is-invalid' => $formErrors->has('work_email')])>
                    @error('work_email')
                        <p class="hp-field__error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="hp-field">
                    <label for="demo-phone">Telephone</label>
                    <input type="tel" id="demo-phone" name="phone" placeholder="+267" value="{{ old('phone') }}" @class(['is-invalid' => $formErrors->has('phone')])>
                    @error('phone')
                        <p class="hp-field__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="hp-form__row hp-field">
                <label for="demo-edition">Edition</label>
                <select id="demo-edition" name="edition" @class(['is-invalid' => $formErrors->has('edition')])>
                    @foreach ($editions as $edition)
                        <option value="{{ $edition }}" @selected(old('edition') === $edition)>{{ $edition }}</option>
                    @endforeach
                </select>
                @error('edition')
                    <p class="hp-field__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="hp-form__row hp-field">
                <label for="demo-notes">What should we cover?</label>
                <textarea id="demo-notes" name="notes" rows="3" placeholder="Examinations, fees, tertiary registration…" @class(['is-invalid' => $formErrors->has('notes')])>{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="hp-field__error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="hp-btn hp-btn--block hp-btn--solid">Request my demo</button>
            <p class="hp-form__note">We reply within one business day. No newsletter, no reseller calls.</p>
        </form>
    </div>
</section>
