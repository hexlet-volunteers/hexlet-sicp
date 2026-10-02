import { Head, useForm } from '@inertiajs/react'
import { Anchor, Button, Card, Grid, Image, Stack, TextInput, Title } from '@mantine/core'
import { useTranslation } from 'react-i18next'
import SettingsLayout from '@/layouts/SettingsLayout'
import { useTView } from '@/lib/scope'

export default function ProfileIndex({
  name,
  email,
  github_name,
  profileImage,
  updateUrl,
  menu,
}: App.DTO.Settings.ProfilePageData) {
  const { t } = useTranslation()
  const tView = useTView()
  const form = useForm({ name, github_name: github_name ?? '' })

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    form.patch(updateUrl, { preserveScroll: true })
  }

  return (
    <SettingsLayout menu={menu}>
      <Head title={t('account.profile')} />
      <Grid>
        <Grid.Col span={{ base: 12, md: 8 }}>
          <Card withBorder>
            <Title order={3} mb="md">
              {t('account.profile')} {email}
            </Title>
            <form onSubmit={submit}>
              <Stack>
                <TextInput
                  label={tView('.name')}
                  name="name"
                  value={form.data.name}
                  onChange={(e) => form.setData('name', e.currentTarget.value)}
                  error={form.errors.name}
                />
                <TextInput
                  label={tView('.github_name')}
                  name="github_name"
                  value={form.data.github_name}
                  onChange={(e) => form.setData('github_name', e.currentTarget.value)}
                  error={form.errors.github_name}
                />
                <Button type="submit" loading={form.processing} w="fit-content">
                  {t('layout.common.save')}
                </Button>
              </Stack>
            </form>
          </Card>
        </Grid.Col>
        <Grid.Col span={{ base: 12, md: 4 }}>
          <Card withBorder>
            <Card.Section>
              <Image src={profileImage} alt={`${name} avatar`} />
            </Card.Section>
            <Anchor href="https://gravatar.com" target="_blank" rel="noopener noreferrer" mt="md">
              {t('account.go_to_gravatar')}
            </Anchor>
          </Card>
        </Grid.Col>
      </Grid>
    </SettingsLayout>
  )
}
