<x-app-layout>
  <x-app-container>
    <x-page-heading title="プロフィール" />
    <x-card>
      @include('profile.partials.update-profile-information-form')
    </x-card>

    <x-card>
      @include('profile.partials.update-password-form')
    </x-card>

    <x-card>
      @include('profile.partials.delete-user-form')
    </x-card>
  </x-app-container>
</x-app-layout>
