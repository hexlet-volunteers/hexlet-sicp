import { Head, Link } from '@inertiajs/react'
import { Anchor, Group, Title } from '@mantine/core'
import { useTranslation } from 'react-i18next'
import { type Column, DataTable } from '@/components/ui/DataTable'
import { Pagination } from '@/components/ui/Pagination'
import AppLayout from '@/layouts/AppLayout'

type Solution = App.DTO.My.SolutionListItemData

export default function MySolutionIndex({
  userName,
  userUrl,
  progressUrl,
  items,
  pagination,
}: App.DTO.My.SolutionListPageData) {
  const { t } = useTranslation()

  const columns: Column<Solution>[] = [
    {
      label: t('progresses.exercises'),
      render: (solution) =>
        `${t('progresses.exercise')} ${solution.exerciseTitle} (${t('progresses.chapter')} ${solution.chapterPath})`,
    },
    {
      label: t('progresses.solutions'),
      // users.solutions.show уже на Inertia — <Link>.
      render: (solution) => (
        <Anchor component={Link} href={solution.showUrl}>
          {t('progresses.see_details')}
        </Anchor>
      ),
    },
  ]

  return (
    <AppLayout>
      <Head title={t('progresses.my_solutions')} />
      <Group justify="space-between" my="md">
        <Title order={1} size="h3">
          {t('layout.nav.my_progress')}
        </Title>
        {/* Профиль ещё на Blade — обычная ссылка. */}
        <Anchor href={userUrl} size="lg">
          {userName}
        </Anchor>
      </Group>
      <Title order={2} size="h4" mb="md">
        <Anchor component={Link} href={progressUrl}>
          {t('progresses.chapters')}
        </Anchor>{' '}
        / {t('progresses.my_solutions')}
      </Title>
      <DataTable columns={columns} items={items} empty={t('progresses.no_solutions')} />
      <Pagination pagination={pagination} />
    </AppLayout>
  )
}
