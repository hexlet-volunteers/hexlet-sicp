import '@inertiajs/core'

declare module '@inertiajs/core' {
  export interface InertiaConfig {
    sharedPageProps: {
      auth: { user: App.DTO.AuthUserData | null }
      locale: string
      scope: string | null
      translations: Record<string, Record<string, unknown>>
      nav: App.DTO.Navigation.NavigationData
      colorScheme: 'light' | 'dark'
      csrfToken: string
      flash: { message: string; level: 'success' | 'error' | 'warning' | 'info' } | null
    }
  }
}
