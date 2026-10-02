import { Head } from '@inertiajs/react'
import { Anchor, Box, Group, Text, Title } from '@mantine/core'
import { useTranslation } from 'react-i18next'
import { type Column, DataTable } from '@/components/ui/DataTable'
import { Filter } from '@/components/ui/Filter'
import { Pagination } from '@/components/ui/Pagination'
import AdminLayout from '@/layouts/AdminLayout'

type Comment = App.DTO.Admin.CommentListItemData

export default function AdminCommentIndex({
  items,
  pagination,
  filter,
  filterUrl,
  menu,
}: App.DTO.Admin.CommentListPageData) {
  const { t } = useTranslation()

  // Профиль, глава/упражнение и комментарий ещё на Blade — обычные ссылки, не <Link>.
  const columns: Column<Comment>[] = [
    { label: t('admin.comments.table.id'), render: (comment) => comment.id },
    {
      label: t('admin.comments.table.user'),
      render: (comment) => (
        <Anchor href={comment.userUrl} fw={700}>
          {comment.userName}
        </Anchor>
      ),
    },
    {
      label: t('admin.comments.table.commentable'),
      render: (comment) => (
        <Anchor href={comment.commentableUrl} fw={700}>
          {comment.commentableName}
        </Anchor>
      ),
    },
    {
      label: t('admin.comments.table.content'),
      render: (comment) => (
        <Anchor href={comment.url} maw={300} display="block">
          {/* biome-ignore lint/security/noDangerouslySetInnerHtml: contentHtml — Parsedown в safe mode, см. CommentListItemData */}
          <Box dangerouslySetInnerHTML={{ __html: comment.contentHtml }} />
          &#8230;
        </Anchor>
      ),
    },
    {
      label: t('admin.comments.table.created'),
      render: (comment) => (
        <Text size="sm" c="dimmed">
          {comment.createdAt}
        </Text>
      ),
    },
  ]

  return (
    <AdminLayout menu={menu}>
      <Head title={t('admin.comments.title')} />
      <Group justify="space-between" mb="md">
        <Title order={1} size="h2">
          {t('admin.comments.title')}
        </Title>
        <Text c="dimmed">
          {t('admin.comments.total')}: {pagination.total}
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
      <DataTable columns={columns} items={items} empty={t('admin.comments.empty')} highlightOnHover />
      <Pagination pagination={pagination} />
    </AdminLayout>
  )
}
