<script>
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import Alert from '../../components/ui/alert.svelte';
  import Button from '../../components/ui/button.svelte';
  import { router } from '@inertiajs/svelte';

  const goHome = () => {
    router.visit('/');
  };
</script>

<AppLayout title="App Onboarding">
  <div class="mx-auto max-w-3xl space-y-6">
    <Alert variant="warning">
      <span slot="title">Onboarding required</span>
      Replace this page with your onboarding wizard. Use the endpoints below to
      bootstrap Auth Bridge and unlock the rest of the app.
    </Alert>

    <div class="rounded-lg border border-dashed border-border bg-muted/40 p-6 space-y-4">
      <h2 class="text-xl font-semibold">Suggested flow</h2>
      <ol class="list-decimal list-inside space-y-2 text-sm text-muted-foreground">
        <li>Collect Auth API URL and admin credentials.</li>
        <li>POST to <code>/onboarding/token</code> to retrieve a bootstrap token.</li>
        <li>Gather app metadata (name, key, redirect URI, account IDs).</li>
        <li>POST to <code>/onboarding/run</code> with the collected values.</li>
      </ol>
    </div>

    <div class="space-y-2 text-sm text-muted-foreground">
      <p><strong>POST</strong> <code>/onboarding/token</code> → returns <code>{`{ token: string }`}</code></p>
      <p>
        <strong>POST</strong> <code>/onboarding/run</code> → runs
        <code>auth-bridge:onboard</code>, clears caches, and writes the onboarding lock.
      </p>
      <p class="italic">Once onboarding completes this page will redirect to the home route.</p>
    </div>

    <Button variant="secondary" on:click={goHome}>
      Back to home
    </Button>
  </div>
</AppLayout>
