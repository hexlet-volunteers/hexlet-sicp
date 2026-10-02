import { usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'

// Ключ с точкой в начале — относительно shared prop `scope`: tView('.name') → t('settings.profile.name').
export function useTView() {
  const { scope } = usePage().props
  const { t } = useTranslation()

  return (key: string) => t(key.startsWith('.') && scope ? `${scope}${key}` : key)
}
