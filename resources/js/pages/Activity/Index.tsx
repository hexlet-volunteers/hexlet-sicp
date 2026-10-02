import { Head } from '@inertiajs/react'
import { Anchor, List, Text, Title } from '@mantine/core'
import { useTranslation } from 'react-i18next'
import { type Column, DataTable } from '@/components/ui/DataTable'
import { Pagination } from '@/components/ui/Pagination'
import AppLayout from '@/layouts/AppLayout'

type Item = App.DTO.Activity.ActivityItemData

// Ссылки ведут на Blade-страницы и на внешнюю книгу — обычный <a>, не <Link>.
function ActivityLink({ link }: { link: App.DTO.Activity.ActivityLinkData }) {
  return link.href ? <Anchor href={link.href}>{link.label}</Anchor> : <Text span>{link.label}</Text>
}

function Description({ item }: { item: Item }) {
  if (item.links.length > 1) {
    return (
      <>
        <Text>{item.description}</Text>
        <List size="sm">
          {item.links.map((link) => (
            <List.Item key={link.label}>
              <ActivityLink link={link} />
            </List.Item>
          ))}
        </List>
      </>
    )
  }

  return (
    <Text>
      {item.description} {item.links[0] && <ActivityLink link={item.links[0]} />}
    </Text>
  )
}

export default function ActivityIndex({ items, pagination }: App.DTO.Activity.ActivityPageData) {
  const { t } = useTranslation()

  const columns: Column<Item>[] = [
    {
      label: t('activitylog.user'),
      render: (item) => item.causerUrl && <Anchor href={item.causerUrl}>{item.causerName}</Anchor>,
    },
    { label: t('activitylog.description'), render: (item) => <Description item={item} /> },
    { label: t('activitylog.time'), render: (item) => item.createdAt },
  ]

  return (
    <AppLayout>
      <Head title={t('activitylog.title')} />
      <Title order={1} size="h3" my="md">
        {t('activitylog.title')}
      </Title>
      <DataTable columns={columns} items={items} striped />
      <Pagination pagination={pagination} />
    </AppLayout>
  )
}
