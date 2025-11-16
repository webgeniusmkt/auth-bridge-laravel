<script>
  import { Link, page, router } from '@inertiajs/svelte';
  import { onMount } from 'svelte';
  import Button from '../components/ui/button.svelte';
  import Alert from '../components/ui/alert.svelte';

  export let title = '';

  let menuOpen = false;
  let triggerRef;
  let menuRef;

  const logout = () => {
    menuOpen = false;
    router.post('/logout');
  };

  const manageAccountUrl = $page.props?.auth?.portal_url ?? null;
  const manageAccount = () => {
    if (!manageAccountUrl) {
      return;
    }

    window.open(manageAccountUrl, '_blank', 'noopener');
    menuOpen = false;
  };

  const toggleMenu = () => {
    menuOpen = !menuOpen;
  };

  const closeMenu = (event) => {
    if (!menuOpen) {
      return;
    }

    const target = event.target;

    if (menuRef?.contains(target) || triggerRef?.contains(target)) {
      return;
    }

    menuOpen = false;
  };

  onMount(() => {
    document.addEventListener('click', closeMenu);

    return () => document.removeEventListener('click', closeMenu);
  });

  const initials = (value) =>
    value
      .split(' ')
      .map((part) => part[0])
      .join('')
      .slice(0, 2)
      .toUpperCase();

  $: appName = $page.props?.app?.name ?? 'Laravel App';
  $: pageTitle = title ? `${title} · ${appName}` : appName;
  $: flash = $page.props?.flash ?? {};
  $: user = $page.props?.auth?.user ?? null;
  $: avatar = user ? initials((user.name ?? user.email ?? '').trim() || appName) : appName.slice(0, 1);
</script>

<svelte:head>
  <title>{pageTitle}</title>
</svelte:head>

<div class="min-h-screen bg-background text-foreground">
  <header class="border-b bg-card/70 backdrop-blur">
    <div class="container flex h-16 items-center justify-between gap-6">
      <div class="flex items-center gap-3 text-sm">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-primary-foreground font-semibold uppercase">
          {appName.slice(0, 1)}
        </div>
        <div class="leading-tight">
          <Link href="/">
            <p class="text-[0.7rem] uppercase tracking-[0.28em] text-muted-foreground">Powered by Auth Bridge</p>
            <h1 class="text-lg font-semibold leading-tight">{appName}</h1>
          </Link>
        </div>
      </div>
      <div class="flex items-center gap-3 text-sm">
        {#if user}
          <div class="relative">
            <button
              class="flex items-center gap-2 rounded-full border border-border bg-card px-3 py-1.5 text-left text-sm font-medium shadow-sm hover:border-primary transition"
              type="button"
              bind:this={triggerRef}
              on:click={toggleMenu}
            >
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-semibold uppercase text-primary-foreground">
                {avatar}
              </div>
              <div class="hidden flex-col leading-tight md:flex">
                <span>{user.name ?? user.email}</span>
                <span class="text-xs text-muted-foreground">{appName}</span>
              </div>
              <svg class="h-4 w-4 text-muted-foreground" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6 8l4 4 4-4" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
            {#if menuOpen}
              <div
                bind:this={menuRef}
                class="absolute right-0 z-50 mt-2 w-48 rounded-xl border border-border bg-popover p-3 shadow-lg"
              >
                <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-muted-foreground">
                  Account
                </p>
                <button
                  class="mt-2 w-full rounded-md px-2 py-1.5 text-left text-sm font-medium text-foreground hover:bg-muted"
                  type="button"
                  on:click={manageAccount}
                >
                  Manage account
                </button>
                <button
                  class="mt-1 w-full rounded-md px-2 py-1.5 text-left text-sm font-medium text-destructive hover:bg-destructive/10"
                  type="button"
                  on:click={logout}
                >
                  Log out
                </button>
              </div>
            {/if}
          </div>
        {:else}
          <Button href="/login">
            Log in
          </Button>
        {/if}
      </div>
    </div>
  </header>
  <main class="container space-y-6 py-12">
    {#if flash?.success}
      <Alert variant="success">
        <span slot="title">Success</span>
        {flash.success}
      </Alert>
    {/if}
    {#if flash?.error}
      <Alert variant="destructive">
        <span slot="title">Something went wrong</span>
        {flash.error}
      </Alert>
    {/if}
    <slot />
  </main>
</div>
