@php
    $formErrors = $errors ?? new \Illuminate\Support\ViewErrorBag();
@endphp
<section id="contact" class="contact">
    <div class="container contact-inner">
        <div>
            <span class="eyebrow" style="color: rgba(255,255,255,0.7);">{{ $site['contact']['eyebrow'] }}</span>
            <h2 style="margin-top: 14px;">{{ $site['contact']['title'] }}</h2>
            <p>{{ $site['contact']['description'] }}</p>
            <ul class="contact-list">
                @foreach ($site['contact']['details'] as $detail)
                    <li>
                        @include('website.partials.icon', ['name' => $detail['icon'], 'size' => 18])
                        <span><b>{{ $detail['label'] }}</b>{{ $detail['value'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <form class="contact-form" action="{{ route('website.book-demo') }}" method="POST">
            @csrf
            @if (session('book_demo_success') || session('book_demo_error'))
                <div class="contact-feedback">
                    @if (session('book_demo_success'))
                        <div class="contact-alert">{{ session('book_demo_success') }}</div>
                    @endif
                    @if (session('book_demo_error'))
                        <div class="contact-alert error">{{ session('book_demo_error') }}</div>
                    @endif
                </div>
            @endif
            <div class="form-row">
                <div class="form-field">
                    <label>Full name</label>
                    <input type="text" name="full_name" placeholder="Tebogo Molefe" value="{{ old('full_name') }}" @class(['is-invalid' => $formErrors->has('full_name')])>
                    @if ($formErrors->has('full_name'))
                        <div class="field-error">{{ $formErrors->first('full_name') }}</div>
                    @endif
                </div>
                <div class="form-field">
                    <label>Your role</label>
                    <input type="text" name="role" placeholder="Head Teacher" value="{{ old('role') }}" @class(['is-invalid' => $formErrors->has('role')])>
                    @if ($formErrors->has('role'))
                        <div class="field-error">{{ $formErrors->first('role') }}</div>
                    @endif
                </div>
            </div>
            <div class="form-field">
                <label>Institution</label>
                <input type="text" name="institution" placeholder="Francistown Senior School" value="{{ old('institution') }}" @class(['is-invalid' => $formErrors->has('institution')])>
                @if ($formErrors->has('institution'))
                    <div class="field-error">{{ $formErrors->first('institution') }}</div>
                @endif
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label>Work email</label>
                    <input type="email" name="work_email" placeholder="admin@francistown.seniorschool.info" value="{{ old('work_email') }}" @class(['is-invalid' => $formErrors->has('work_email')])>
                    @if ($formErrors->has('work_email'))
                        <div class="field-error">{{ $formErrors->first('work_email') }}</div>
                    @endif
                </div>
                <div class="form-field">
                    <label>Phone</label>
                    <input type="tel" name="phone" placeholder="+267 71 234 567" value="{{ old('phone') }}" @class(['is-invalid' => $formErrors->has('phone')])>
                    @if ($formErrors->has('phone'))
                        <div class="field-error">{{ $formErrors->first('phone') }}</div>
                    @endif
                </div>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label>Edition</label>
                    <select name="edition" @class(['is-invalid' => $formErrors->has('edition')])>
                        @foreach (['Heritage Pro — Schools', 'Heritage Pro — Collegiate', 'Heritage Pro — K-12', 'Not sure yet'] as $option)
                            <option value="{{ $option }}" @selected(old('edition') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    @if ($formErrors->has('edition'))
                        <div class="field-error">{{ $formErrors->first('edition') }}</div>
                    @endif
                </div>
                <div class="form-field">
                    <label>Number of learners</label>
                    <select name="learner_band" @class(['is-invalid' => $formErrors->has('learner_band')])>
                        @foreach (['Under 200', '200 – 500', '500 – 1,500', '1,500 – 5,000', '5,000+'] as $option)
                            <option value="{{ $option }}" @selected(old('learner_band') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    @if ($formErrors->has('learner_band'))
                        <div class="field-error">{{ $formErrors->first('learner_band') }}</div>
                    @endif
                </div>
            </div>
            <div class="form-field">
                <label>Anything we should know?</label>
                <textarea name="notes" placeholder="Current systems, timeline, pain points…" @class(['is-invalid' => $formErrors->has('notes')])>{{ old('notes') }}</textarea>
                @if ($formErrors->has('notes'))
                    <div class="field-error">{{ $formErrors->first('notes') }}</div>
                @endif
            </div>
            <button type="submit" class="btn btn-primary contact-cta">Request demo @include('website.partials.icon', ['name' => 'arrow', 'size' => 14])</button>
            <p style="text-align: center; font-size: 12px; color: var(--fg-3); margin-top: 12px; margin-bottom: 0;">We respond within one business day.</p>
        </form>
    </div>
</section>
