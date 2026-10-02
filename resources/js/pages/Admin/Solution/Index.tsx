import { Head } from '@inertiajs/react'
import { Anchor, Group, Text, Title } from '@mantine/core'
import { useTranslation } from 'react-i18next'
import { type Column, DataTable } from '@/components/ui/DataTable'
import { Filter } from '@/components/ui/Filter'
import { Pagination } from '@/components/ui/Pagination'
import AdminLayout from '@/layouts/AdminLayout'

type Solution = App.DTO.Admin.SolutionListItemData

export default function AdminSolutionIndex({
  items,
  pagination,
  filter,
  filterUrl,
  menu,
}: App.DTO.Admin.SolutionListPageData) {
  const { t } = useTranslation()

  // Профиль, упражнение и решение ещё на Blade — обычные ссылки, не <Link>.
  const columns: Column<Solution>[] = [
    { label: t('admin.solutions.table.id'), render: (solution) => solution.id },
    {
      label: t('admin.solutions.table.user'),
      render: (solution) => (
        <Anchor href={solution.userUrl} fw={700}>
          {solution.userName}
        </Anchor>
      ),
    },
    {
      label: t('admin.solutions.table.exercise'),
      render: (solution) => (
        <>
          <Anchor href={solution.exerciseUrl} fw={700}>
            {solution.exercisePath}
          </Anchor>
          <Text size="sm" c="dimmed">
            {solution.exerciseTitle}
          </Text>
        </>
      ),
    },
    {
      label: t('admin.solutions.table.content'),
      render: (solution) => (
        <Anchor href={solution.url} maw={300} display="block">
          {solution.content} &#8230;
        </Anchor>
      ),
    },
    {
      label: t('admin.solutions.table.created'),
      render: (solution) => (
        <Text size="sm" c="dimmed">
          {solution.createdAt}
        </Text>
      ),
    },
  ]

  return (
    <AdminLayout menu={menu}>
      <Head title={t('admin.solutions.title')} />
      <Group justify="space-between" mb="md">
        <Title order={1} size="h2">
          {t('admin.solutions.title')}
        </Title>
        <Text c="dimmed">
          {t('admin.solutions.total')}: {pagination.total}
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
      <DataTable columns={columns} items={items} empty={t('admin.solutions.empty')} highlightOnHover />
      <Pagination pagination={pagination} />
    </AdminLayout>
  )
}
