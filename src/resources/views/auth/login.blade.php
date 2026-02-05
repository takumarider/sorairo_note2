<x-guest-layout>
    <div class="rounded-3xl bg-white/75 backdrop-blur-lg border border-white/50 shadow-xl p-8 lg:p-12">
        <div class="grid lg:grid-cols-5 gap-8 items-center">
            <div class="lg:col-span-2 space-y-3">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-800 text-sm font-semibold">sorairo_note</span>
                <h1 class="text-3xl lg:text-4xl font-extrabold text-slate-900 leading-snug">
                    ログイン
                    <br class="hidden lg:block" />
                </h1>
                <p class="text-base lg:text-lg text-slate-600">アカウントにログインして、予約確認やキャンセル、メニュー変更もここから。</p>
                <div class="flex gap-3">
                    <a href="/" class="text-sm font-semibold text-sky-700 hover:text-sky-900">トップに戻る</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="text-sm font-semibold text-sky-700 hover:text-sky-900">新規登録</a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-3">
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('labels.status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <x-input-label for="email" :value="__('labels.email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="password" :value="__('labels.password')" />
                        <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500" name="remember">
                            <span class="ms-2 text-sm text-slate-600">{{ __('labels.remember_me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-semibold text-sky-700 hover:text-sky-900" href="{{ route('password.request') }}">
                                {{ __('labels.forgot_password') }}
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <x-primary-button class="px-6 py-2 bg-gradient-to-r from-sky-500 to-cyan-500 text-white font-semibold shadow-lg shadow-sky-200 hover:translate-y-[-1px] transition-transform">
                            {{ __('labels.login') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
