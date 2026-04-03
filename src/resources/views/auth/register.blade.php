<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('labels.name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-hint :message="__('labels.name_hint')" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('labels.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-hint :message="__('labels.email_hint')" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('labels.password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <div class="mt-2 flex justify-end">
                <button
                    id="toggle-password-button"
                    type="button"
                    class="h-8 px-3 rounded-md border border-sky-300 bg-sky-100 text-xs font-semibold text-sky-800 hover:bg-sky-200 focus:outline-none focus:ring-2 focus:ring-sky-500"
                    aria-pressed="false"
                    aria-label="表示"
                >
                    表示
                </button>
            </div>

            <div class="mt-1">
                <p class="text-xs text-gray-500">{{ __('labels.password_hint_title') }}</p>
                <ul class="text-xs text-gray-500 list-disc list-inside ml-1 space-y-0.5">
                    <li>{{ __('labels.password_hint_min') }}</li>
                    <li>{{ __('labels.password_hint_letters') }}</li>
                    <li>{{ __('labels.password_hint_numbers') }}</li>
                    <li>{{ __('labels.password_hint_symbols') }}</li>
                </ul>
            </div>

            <div class="mt-3 flex items-center gap-3">
                <x-secondary-button id="generate-password-button" type="button" class="normal-case tracking-normal text-sm font-bold border-sky-300 bg-sky-100 text-sky-800 hover:bg-sky-200 focus:ring-sky-500">
                    おまかせパスワード
                </x-secondary-button>
                <p id="generated-password-message" class="text-xs text-emerald-700 hidden" aria-live="polite">安全なパスワードを生成して入力しました。</p>
            </div>
            <x-input-hint :message="'クリックすると16文字の安全なパスワードを自動生成します。'" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('labels.confirm_password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <div class="mt-2 flex justify-end">
                <button
                    id="toggle-password-confirmation-button"
                    type="button"
                    class="h-8 px-3 rounded-md border border-sky-300 bg-sky-100 text-xs font-semibold text-sky-800 hover:bg-sky-200 focus:outline-none focus:ring-2 focus:ring-sky-500"
                    aria-pressed="false"
                    aria-label="表示"
                >
                    表示
                </button>
            </div>
            <x-input-hint :message="__('labels.password_confirmation_hint')" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('labels.already_registered') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('labels.register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        (() => {
            const button = document.getElementById('generate-password-button');
            const togglePasswordButton = document.getElementById('toggle-password-button');
            const toggleConfirmationButton = document.getElementById('toggle-password-confirmation-button');
            const passwordInput = document.getElementById('password');
            const confirmationInput = document.getElementById('password_confirmation');
            const message = document.getElementById('generated-password-message');
            const showText = '表示';
            const hideText = '非表示';

            if (!button || !passwordInput || !confirmationInput || !window.crypto) {
                return;
            }

            const uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            const lowercase = 'abcdefghijkmnopqrstuvwxyz';
            const numbers = '23456789';
            const symbols = '!@#$%^&*()-_=+[]{}';
            const allChars = uppercase + lowercase + numbers + symbols;
            const passwordLength = 16;

            function secureRandomInt(max) {
                if (!Number.isInteger(max) || max <= 0) {
                    throw new Error('Invalid max value for secureRandomInt.');
                }

                const values = new Uint32Array(1);
                const limit = Math.floor(0x100000000 / max) * max;

                do {
                    window.crypto.getRandomValues(values);
                } while (values[0] >= limit);

                return values[0] % max;
            }

            function pick(chars) {
                return chars[secureRandomInt(chars.length)];
            }

            function shuffle(text) {
                const chars = text.split('');

                for (let i = chars.length - 1; i > 0; i -= 1) {
                    const j = secureRandomInt(i + 1);
                    [chars[i], chars[j]] = [chars[j], chars[i]];
                }

                return chars.join('');
            }

            function generatePassword() {
                const requiredChars = [
                    pick(uppercase),
                    pick(lowercase),
                    pick(numbers),
                    pick(symbols),
                ];

                while (requiredChars.length < passwordLength) {
                    requiredChars.push(pick(allChars));
                }

                return shuffle(requiredChars.join(''));
            }

            function emitInputEvents(input) {
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function setupVisibilityToggle(toggleButton, input) {
                if (!toggleButton || !input) {
                    return;
                }

                toggleButton.addEventListener('click', () => {
                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    toggleButton.textContent = isHidden ? hideText : showText;
                    toggleButton.setAttribute('aria-label', isHidden ? hideText : showText);
                    toggleButton.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                });
            }

            setupVisibilityToggle(togglePasswordButton, passwordInput);
            setupVisibilityToggle(toggleConfirmationButton, confirmationInput);

            button.addEventListener('click', () => {
                const generated = generatePassword();

                passwordInput.value = generated;
                confirmationInput.value = generated;
                emitInputEvents(passwordInput);
                emitInputEvents(confirmationInput);

                if (message) {
                    message.classList.remove('hidden');
                }
            });
        })();
    </script>
</x-guest-layout>
