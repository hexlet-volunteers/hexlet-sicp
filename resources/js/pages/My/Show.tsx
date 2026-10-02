import { Head } from '@inertiajs/react'
import { Anchor, Badge, Group, List, Paper, Stack, Tabs, Title, type TitleOrder } from '@mantine/core'
import { IconCheck, IconClockHour4 } from '@tabler/icons-react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/layouts/AppLayout'

type ChapterNodeData = App.DTO.Progress.ChapterNodeData

function DoneMark() {
  const { t } = useTranslation()
  return <IconCheck size={16} color="var(--mantine-color-green-7)" aria-label={t('progresses.completed')} />
}

function ChapterCounter({ node }: { node: ChapterNodeData }) {
  const color = node.isCompleted ? 'green' : node.completedChildrenCount > 0 ? 'yellow' : 'gray'
  return (
    <Badge color={color} variant="light">
      {node.completedChildrenCount}/{node.totalChildrenCount}
    </Badge>
  )
}

// Ссылки ведут на Blade-страницы глав и упражнений — обычный <a>, не <Link>.
function ChapterNode({ node, level }: { node: ChapterNodeData; level: number }) {
  const { t } = useTranslation()

  if (node.children.length > 0) {
    return (
      <Stack gap="xs" mt="md">
        <Group gap="xs">
          {/* Как в Blade: заголовок h4 для главы, ниже по уровню — h5, h6. */}
          <Title order={Math.min(level + 3, 6) as TitleOrder}>{node.title}</Title>
          <ChapterCounter node={node} />
        </Group>
        {node.children.map((child) => (
          <ChapterNode key={child.id} node={child} level={level + 1} />
        ))}
      </Stack>
    )
  }

  return (
    <Paper withBorder p="sm">
      <Group gap={4}>
        <Anchor href={node.url}>{node.title}</Anchor>
        {node.isCompleted && <DoneMark />}
      </Group>
      {node.exercises.length > 0 && (
        <List listStyleType="none" size="sm" ml="sm">
          {node.exercises.map((exercise) => (
            <List.Item key={exercise.id}>
              <Group gap={4}>
                <Anchor href={exercise.url} c="dimmed" size="sm">
                  {exercise.title}
                </Anchor>
                {exercise.isInProgress && <IconClockHour4 size={16} aria-label={t('progresses.in_progress')} />}
                {exercise.isCompleted && <DoneMark />}
              </Group>
            </List.Item>
          ))}
        </List>
      )}
    </Paper>
  )
}

export default function MyShow({ userName, userUrl, solutionsUrl, chapters }: App.DTO.Progress.MyProgressPageData) {
  const { t } = useTranslation()

  return (
    <AppLayout>
      <Head title={t('layout.nav.my_progress')} />
      <Group justify="space-between" my="md">
        <Title order={1} size="h3">
          {t('layout.nav.my_progress')}
        </Title>
        <Anchor href={userUrl} size="lg">
          {userName}
        </Anchor>
      </Group>
      <Title order={2} size="h4" mb="md">
        {t('progresses.chapters')} / <Anchor href={solutionsUrl}>{t('progresses.my_solutions')}</Anchor>
      </Title>

      {chapters.length > 0 && (
        <Tabs
          orientation="vertical"
          defaultValue={String(chapters[0].id)}
          styles={{ tab: { justifyContent: 'flex-start', whiteSpace: 'normal' }, tabLabel: { textAlign: 'start' } }}
        >
          <Tabs.List w={{ base: '40%', md: 320 }}>
            {chapters.map((chapter) => (
              <Tabs.Tab key={chapter.id} value={String(chapter.id)}>
                {chapter.title}
              </Tabs.Tab>
            ))}
          </Tabs.List>
          {chapters.map((chapter) => (
            <Tabs.Panel key={chapter.id} value={String(chapter.id)} px="md">
              <ChapterNode node={chapter} level={1} />
            </Tabs.Panel>
          ))}
        </Tabs>
      )}
    </AppLayout>
  )
}
