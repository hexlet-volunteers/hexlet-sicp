import { Link } from '@inertiajs/react'
import { Card, NavLink, Text } from '@mantine/core'
import { ItemIcon } from './ItemIcon'

type Props = {
  menu: App.DTO.Navigation.NavItemData[]
  title?: string
}

export function SideMenu({ menu, title }: Props) {
  return (
    <Card withBorder shadow="sm" p={0}>
      {title && (
        <Text fw={700} c="dimmed" px="md" py="sm">
          {title}
        </Text>
      )}
      {menu.map((item) => {
        const props = {
          href: item.href,
          label: item.label,
          active: item.active,
          leftSection: <ItemIcon name={item.icon} />,
          variant: 'filled',
        } as const

        return item.inertia ? (
          <NavLink key={item.href} component={Link} {...props} />
        ) : (
          <NavLink key={item.href} {...props} />
        )
      })}
    </Card>
  )
}
