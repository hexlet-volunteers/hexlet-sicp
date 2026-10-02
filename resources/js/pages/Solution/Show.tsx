import { Head } from '@inertiajs/react'
import { Anchor, Divider, Grid, Group, Tabs, Text, Title } from '@mantine/core'
import { useTranslation } from 'react-i18next'
import { CodeBlock } from '@/components/ui/CodeBlock'
import AppLayout from '@/layouts/AppLayout'

type Version = App.DTO.Solution.SolutionVersionData

// Mantine генерирует id вкладок и панелей на каждый <Tabs> сам, поэтому две панели не конфликтуют.
function VersionTabs({ versions }: { versions: Version[] }) {
  return (
    <Tabs variant="pills" defaultValue={String(versions[0]?.id)}>
      <Tabs.List mb="md">
        {versions.map((version, index) => (
          <Tabs.Tab key={version.id} value={String(version.id)}>
            v.{index + 1}
          </Tabs.Tab>
        ))}
      </Tabs.List>
      {versions.map((version) => (
        <Tabs.Panel key={version.id} value={String(version.id)}>
          <CodeBlock code={version.content} />
        </Tabs.Panel>
      ))}
    </Tabs>
  )
}

export default function SolutionShow({
  title,
  exerciseTitle,
  exerciseUrl,
  userName,
  userUrl,
  versions,
}: App.DTO.Solution.SolutionShowPageData) {
  const { t } = useTranslation()
  const compare = versions.length > 1

  return (
    <AppLayout>
      <Head title={title} />
      <Group justify="space-between" my="md">
        {/* Упражнение и профиль — Blade-страницы: обычный <a>. */}
        <Anchor href={exerciseUrl} size="lg">
          {t('solution.exercise')} {exerciseTitle}
        </Anchor>
        <Anchor href={userUrl} size="lg">
          {userName}
        </Anchor>
      </Group>
      <Title order={2} ta="center">
        {t('solution.code_review')}
      </Title>
      <Text ta="center" size="lg">
        {t('solution.sub_title')}
      </Text>
      <Divider my="md" />
      {/* В режиме сравнения обе панели видны одновременно: по версии в каждой. */}
      <Grid>
        <Grid.Col span={{ base: 12, md: compare ? 6 : 12 }}>
          <VersionTabs versions={versions} />
        </Grid.Col>
        {compare && (
          <Grid.Col span={{ base: 12, md: 6 }}>
            <VersionTabs versions={versions} />
          </Grid.Col>
        )}
      </Grid>
    </AppLayout>
  )
}
