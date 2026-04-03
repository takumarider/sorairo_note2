<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">パスワードの更新</h2>

        <p class="mt-1 text-sm text-gray-600">
            安全のため、十分に長く推測されにくいパスワードを設定してください。
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="現在のパスワード" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="新しいパスワード" />

            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <div class="mt-2 flex justify-end">
                <button
                    id="update-toggle-password-button"
                    type="button"
                    class="h-8 px-3 rounded-md border border-sky-300 bg-sky-100 text-xs font-semibold text-sky-800 hover:bg-sky-200 focus:outline-none focus:ring-2 focus:ring-sky-500"
                    aria-pressed="false"
                    aria-label="表示"
                >
                    表示
                </button>
            </div>

            <div class="mt-3 flex items-center gap-3">
                <x-secondary-button id="update-generate-password-button" type="button" class="normal-case tracking-normal text-sm font-bold border-sky-300 bg-sky-100 text-sky-800 hover:bg-sky-200 focus:ring-sky-500">
                    おまかせパスワード
                </x-secondary-button>
                <p id="update-generated-password-message" class="text-xs text-emerald-700 hidden" aria-live="polite">安全なパスワードを生成して入力しました。</p>
            </div>
            <x-input-hint :message="'クリックすると16文字の安全なパスワードを自動生成します。'" />

            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="パスワード（確認用）" />

            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <div class="mt-2 flex justify-end">
                <button
                    id="update-toggle-password-confirmation-button"
                    type="button"
                    class="h-8 px-3 rounded-md border border-sky-300 bg-sky-100 text-xs font-semibold text-sky-800 hover:bg-sky-200 focus:outline-none focus:ring-2 focus:ring-sky-500"
                    aria-pressed="false"
                    aria-label="表示"
                >
                    表示
                </button>
            </div>

            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>保存</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >保存しました。</p>
            @endif
        </div>
    </form>

    <script>
        (() => {
            const button = document.getElementById('update-generate-password-button');
            const togglePasswordButton = document.getElementById('update-toggle-password-button');
            const toggleConfirmationButton = document.getElementById('update-toggle-password-confirmation-button');
            const passwordInput = document.getElementById('update_password_password');
            const confirmationInput = document.getElementById('update_password_password_confirmation');
            const message = document.getElementById('update-generated-password-message');
            const showText = '表示';
            const hideText = '非表示';

            if (!passwordInput || !confirmationInput) {
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

            if (!button || !window.crypto) {
                return;
            }

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
</section>
