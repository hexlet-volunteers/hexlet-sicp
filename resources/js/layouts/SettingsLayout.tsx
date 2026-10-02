import { Grid } from '@mantine/core'
import { useTranslation } from 'react-i18next'
import { SideMenu } from '@/components/ui/SideMenu'
import AppLayout from './AppLayout'

type Props = {
  menu: App.DTO.Navigation.NavItemData[]
  children: React.ReactNode
}

export default function SettingsLayout({ menu, children }: Props) {
  const { t } = useTranslation()

  return (
    <AppLayout>
      <Grid my="md">
        <Grid.Col span={{ base: 12, md: 3 }}>
          <SideMenu menu={menu} title={t('account.settings')} />
        </Grid.Col>
        <Grid.Col span={{ base: 12, md: 9 }}>{children}</Grid.Col>
      </Grid>
    </AppLayout>
  )
}
