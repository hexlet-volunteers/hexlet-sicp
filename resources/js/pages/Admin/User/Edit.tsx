import { Head, Link, useForm } from '@inertiajs/react'
import { Button, Card, Checkbox, Group, Stack, TextInput, Title } from '@mantine/core'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/layouts/AdminLayout'

export default function AdminUserEdit({
  name,
  githubName,
  isAdmin,
  updateUrl,
  cancelUrl,
  menu,
}: App.DTO.Admin.UserEditPageData) {
  const { t } = useTranslation()
  // Ключи — имена полей UpdateUserData: по ним Laravel раскладывает ошибки.
  const form = useForm({ name, github_name: githubName ?? '', is_admin: isAdmin })

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    form.put(updateUrl)
  }

  return (
    <AdminLayout menu={menu}>
      <Head title={t('admin.users.edit')} />
      <Card withBorder>
        <Title order={1} size="h3" mb="md">
          {t('admin.users.edit')}
        </Title>
        <form onSubmit={submit}>
          <Stack>
            <TextInput
              label={t('account.name')}
              name="name"
              required
              value={form.data.name}
              onChange={(e) => form.setData('name', e.currentTarget.value)}
              error={form.errors.name}
            />
            <TextInput
              label={t('account.github_name')}
              name="github_name"
              value={form.data.github_name}
              onChange={(e) => form.setData('github_name', e.currentTarget.value)}
              error={form.errors.github_name}
            />
            <Checkbox
              label={t('account.admin')}
              name="is_admin"
              checked={form.data.is_admin}
              onChange={(e) => form.setData('is_admin', e.currentTarget.checked)}
              error={form.errors.is_admin}
            />
            <Group>
              <Button type="submit" loading={form.processing}>
                {t('layout.common.save')}
              </Button>
              <Button component={Link} href={cancelUrl} variant="default">
                {t('layout.common.cancel')}
              </Button>
            </Group>
          </Stack>
        </form>
      </Card>
    </AdminLayout>
  )
}
