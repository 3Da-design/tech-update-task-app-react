<x-guest-layout>
  <x-slot name="header">メール認証</x-slot>

  <p class="mb-6 text-sm text-gray-600">
    ご登録ありがとうございます。ご利用前に、送信したメール内のリンクからメールアドレスを認証してください。メールが届かない場合は、再送できます。
  </p>

  @if (session('status') == 'verification-link-sent')
    <x-flash-message class="mb-4">
      認証用リンクを再送しました。
    </x-flash-message>
  @endif

  <form method="POST" action="{{ route('verification.send') }}">
    @csrf

    <x-primary-button class="w-full justify-center">
      認証メールを再送
    </x-primary-button>
  </form>
</x-guest-layout>
