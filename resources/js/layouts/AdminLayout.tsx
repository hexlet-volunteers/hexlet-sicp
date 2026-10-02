import { Grid } from '@mantine/core'
import { SideMenu } from '@/components/ui/SideMenu'
import AppLayout from './AppLayout'

type Props = {
  menu: App.DTO.Navigation.NavItemData[]
  children: React.ReactNode
}

export default function AdminLayout({ menu, children }: Props) {
  return (
    <AppLayout>
      <Grid my="md">
        <Grid.Col span={{ base: 12, md: 3, lg: 2 }}>
          <SideMenu menu={menu} />
        </Grid.Col>
        <Grid.Col span={{ base: 12, md: 9, lg: 10 }}>{children}</Grid.Col>
      </Grid>
    </AppLayout>
  )
}
