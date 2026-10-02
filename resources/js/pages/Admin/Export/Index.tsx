import { Head, usePage } from '@inertiajs/react'
import { Button, Card, NativeSelect, Stack, Title } from '@mantine/core'
import { IconDownload } from '@tabler/icons-react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/layouts/AdminLayout'

// Ответ — файл на скачивание, Inertia-запрос его не переварит: нативная форма с CSRF-токеном.
// NativeSelect, а не Select: только он рендерит настоящий <select name>.
export default function AdminExportIndex({ types, storeUrl, menu }: App.DTO.Admin.ExportPageData) {
  const { t } = useTranslation()
  const { csrfToken } = usePage().props

  return (
    <AdminLayout menu={menu}>
      <Head title={t('admin.export.title')} />
      <Card withBorder>
        <Title order={1} size="h3" mb="md">
          {t('admin.export.title')}
        </Title>
        <form method="post" action={storeUrl}>
          <input type="hidden" name="_token" value={csrfToken} />
          <Stack>
            <NativeSelect label={t('admin.export.select')} name="type" data={types} required />
            <Button type="submit" leftSection={<IconDownload size={16} />} w="fit-content">
              {t('admin.export.button')}
            </Button>
          </Stack>
        </form>
      </Card>
    </AdminLayout>
  )
}
