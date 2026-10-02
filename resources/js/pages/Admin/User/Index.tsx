import { Head, Link } from '@inertiajs/react'
import { ActionIcon, Anchor, Badge, Group, Text, Title } from '@mantine/core'
import { IconPencil, IconShieldCheck } from '@tabler/icons-react'
import { useTranslation } from 'react-i18next'
import { type Column, DataTable } from '@/components/ui/DataTable'
import { Filter } from '@/components/ui/Filter'
import { Pagination } from '@/components/ui/Pagination'
import AdminLayout from '@/layouts/AdminLayout'

type User = App.DTO.Admin.UserListItemData

export default function AdminUserIndex({ items, pagination, filter, filterUrl, menu }: App.DTO.Admin.UserListPageData) {
  const { t } = useTranslation()

  // Профиль пользователя ещё на Blade — обычная ссылка, не <Link>.
  const columns: Column<User>[] = [
    { label: t('admin.users.table.id'), render: (user) => user.id },
    {
      label: t('admin.users.table.name'),
      render: (user) => (
        <Anchor href={user.showUrl} fw={700}>
          {user.name}
        </Anchor>
      ),
    },
    {
      label: t('admin.users.table.email'),
      render: (user) => <Text style={{ wordBreak: 'break-all' }}>{user.email}</Text>,
    },
    {
      label: t('admin.users.table.role'),
      render: (user) =>
        user.isAdmin ? (
          <Badge color="red" leftSection={<IconShieldCheck size={12} />}>
            {t('admin.users.role.admin')}
          </Badge>
        ) : (
          <Badge color="gray">{t('admin.users.role.user')}</Badge>
        ),
    },
    {
      label: t('admin.users.table.created'),
      render: (user) => (
        <Text size="sm" c="dimmed">
          {user.createdAt}
        </Text>
      ),
    },
    {
      label: t('admin.users.table.actions'),
      render: (user) => (
        <ActionIcon
          component={Link}
          href={user.editUrl}
          variant="outline"
          aria-label={t('admin.users.edit')}
          title={t('admin.users.edit')}
        >
          <IconPencil size={16} />
        </ActionIcon>
      ),
    },
  ]

  return (
    <AdminLayout menu={menu}>
      <Head title={t('admin.users.title')} />
      <Group justify="space-between" mb="md">
        <Title order={1} size="h2">
          {t('admin.users.title')}
        </Title>
        <Text c="dimmed">
          {t('admin.users.total')}: {pagination.total}
        </Text>
      </Group>
      <Filter
        action={filterUrl}
        values={filter}
        fields={[
          { name: 'name', placeholder: t('admin.filter.user_name') },
          { name: 'email', placeholder: t('admin.filter.user_email') },
        ]}
      />
      <DataTable columns={columns} items={items} empty={t('admin.users.empty')} highlightOnHover />
      <Pagination pagination={pagination} />
    </AdminLayout>
  )
}
