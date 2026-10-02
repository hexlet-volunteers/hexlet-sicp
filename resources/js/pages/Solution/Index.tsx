import { Head, Link } from '@inertiajs/react'
import { Anchor, Avatar, Button, Group, Paper, Title } from '@mantine/core'
import { useTranslation } from 'react-i18next'
import { type Column, DataTable } from '@/components/ui/DataTable'
import { Filter } from '@/components/ui/Filter'
import { Pagination } from '@/components/ui/Pagination'
import AppLayout from '@/layouts/AppLayout'

type Solution = App.DTO.Solution.SolutionListItemData

// Вкладки из exercise/navigation.blade.php: «Упражнения» ещё на Blade — <a>, «Решения» — <Link>.
function Tabs({ tabs }: { tabs: App.DTO.Navigation.NavItemData[] }) {
  return (
    <Paper bg="var(--mantine-color-default-hover)" p="xs" mb="md">
      <Group grow gap="xs">
        {tabs.map((tab) => {
          const props = {
            href: tab.href,
            variant: tab.active ? 'filled' : 'subtle',
            'aria-current': tab.active ? ('page' as const) : undefined,
            children: tab.label,
          }

          return tab.inertia ? (
            <Button key={tab.href} component={Link} {...props} />
          ) : (
            <Button key={tab.href} component="a" {...props} />
          )
        })}
      </Group>
    </Paper>
  )
}

export default function SolutionIndex({
  items,
  pagination,
  filter,
  filterUrl,
  exercises,
  tabs,
}: App.DTO.Solution.SolutionListPageData) {
  const { t } = useTranslation()

  // Профиль, упражнение и решение ещё на Blade — обычные ссылки, не <Link>.
  const columns: Column<Solution>[] = [
    {
      label: t('views.solution.index.table_header.author'),
      render: (solution) => (
        <Anchor href={solution.userUrl} underline="never">
          <Group gap="xs" wrap="nowrap">
            <Avatar src={solution.userAvatarUrl} alt="" size={30} />
            {solution.userName}
          </Group>
        </Anchor>
      ),
    },
    {
      label: t('views.solution.index.table_header.exercise'),
      render: (solution) => <Anchor href={solution.exerciseUrl}>{solution.exerciseTitle}</Anchor>,
    },
    { label: t('views.solution.index.table_header.date'), render: (solution) => solution.createdAt },
    {
      label: '',
      render: (solution) => <Anchor href={solution.showUrl}>{t('views.solution.index.show_action')}</Anchor>,
    },
  ]

  return (
    <AppLayout>
      <Head title={t('views.solution.index.header.h1')} />
      <Tabs tabs={tabs} />
      <Title order={1} size="h3" mb="md">
        {t('views.solution.index.header.h1')}
      </Title>
      <Filter
        action={filterUrl}
        values={{ 'user.name': filter.userName, exercise_id: filter.exerciseId }}
        fields={[
          { name: 'user.name', placeholder: t('views.solution.index.filter.user') },
          { name: 'exercise_id', placeholder: t('views.solution.index.filter.exercise'), options: exercises },
        ]}
      />
      <DataTable columns={columns} items={items} striped />
      <Pagination pagination={pagination} />
    </AppLayout>
  )
}
