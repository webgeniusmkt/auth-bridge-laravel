<script>
  import AppLayout from '../Layouts/AppLayout.svelte';
  import Button from '../components/ui/button.svelte';
  import Alert from '../components/ui/alert.svelte';

const goToLogin = () => {
  window.location.href = '/login';
};

const goToRegister = () => {
  window.location.href = '/register';
};
</script>

<AppLayout title="Welcome">
  <div class="mx-auto max-w-3xl text-center space-y-6">
    <Alert variant="success">
      <span slot="title">You're ready to sign in</span>
      Onboarding is complete and the Auth Bridge middleware has unlocked the normal homepage.
    </Alert>

    <div class="space-y-4">
      <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">
        Welcome to your application
      </h2>
      <p class="text-lg text-muted-foreground">
        Sign in with your Auth API account or create a new one to get started.
      </p>
    </div>

    <div class="flex flex-wrap items-center justify-center gap-3">
      <Button on:click={goToLogin}>
        Login
      </Button>
      <Button variant="secondary" on:click={goToRegister}>
        Register
      </Button>
    </div>
  </div>
</AppLayout>
