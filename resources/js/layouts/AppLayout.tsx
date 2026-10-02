import { usePage } from '@inertiajs/react'
import {
  Anchor,
  AppShell,
  Burger,
  Container,
  Group,
  Image,
  Menu,
  SimpleGrid,
  Stack,
  Text,
  UnstyledButton,
} from '@mantine/core'
import { useDisclosure } from '@mantine/hooks'
import { notifications } from '@mantine/notifications'
import { IconChevronDown, IconUser } from '@tabler/icons-react'
import { useEffect } from 'react'
import { ItemIcon } from '@/components/ui/ItemIcon'
import { NavAnchor, NavMenuItem } from '@/components/ui/NavAnchor'

const flashColors = { success: 'green', error: 'red', warning: 'yellow', info: 'blue' } as const

function MainNav({ items }: { items: App.DTO.Navigation.NavItemData[] }) {
  return items.map((item) =>
    item.children.length > 0 ? (
      <Menu key={item.href} position="bottom-start">
        <Menu.Target>
          <UnstyledButton>
            <Group gap={4}>
              <ItemIcon name={item.icon} />
              <Text>{item.label}</Text>
              <IconChevronDown size={14} />
            </Group>
          </UnstyledButton>
        </Menu.Target>
        <Menu.Dropdown>
          {item.children.map((child) => (
            <NavMenuItem key={child.href} item={child} leftSection={<ItemIcon name={child.icon} />} />
          ))}
        </Menu.Dropdown>
      </Menu>
    ) : (
      <NavAnchor key={item.href} item={item} c="dark" />
    ),
  )
}

function UserNav({ nav }: { nav: App.DTO.Navigation.NavigationData }) {
  const { auth } = usePage().props

  if (!auth.user) {
    return nav.user.map((item) => <NavAnchor key={item.href} item={item} c="dark" />)
  }

  const [profile, ...rest] = nav.user

  return (
    <Menu position="bottom-end">
      <Menu.Target>
        <UnstyledButton aria-label={auth.user.name}>
          <IconUser size={24} />
        </UnstyledButton>
      </Menu.Target>
      <Menu.Dropdown>
        <NavMenuItem item={profile} />
        <Menu.Divider />
        {rest.map((item) => (
          <NavMenuItem key={item.href} item={item} />
        ))}
      </Menu.Dropdown>
    </Menu>
  )
}

function LocaleNav({ nav }: { nav: App.DTO.Navigation.NavigationData }) {
  return (
    <Menu position="bottom-end">
      <Menu.Target>
        <UnstyledButton>
          <Image src={nav.currentLocale.flagUrl} alt={nav.currentLocale.label} w={24} />
        </UnstyledButton>
      </Menu.Target>
      <Menu.Dropdown>
        {nav.otherLocales.map((locale) => (
          <Menu.Item
            key={locale.code}
            component="a"
            href={locale.href}
            hrefLang={locale.code}
            rel="alternate"
            leftSection={<Image src={locale.flagUrl} alt="" w={24} />}
          >
            {locale.label}
          </Menu.Item>
        ))}
      </Menu.Dropdown>
    </Menu>
  )
}

export default function AppLayout({ children }: { children: React.ReactNode }) {
  const { nav, flash } = usePage().props
  const [opened, { toggle }] = useDisclosure()

  useEffect(() => {
    if (flash) {
      notifications.show({ message: flash.message, color: flashColors[flash.level] })
    }
  }, [flash])

  return (
    <AppShell
      header={{ height: 60 }}
      navbar={{ width: 300, breakpoint: 'lg', collapsed: { desktop: true, mobile: !opened } }}
    >
      <AppShell.Header>
        <Container size="xl" h="100%">
          <Group h="100%" justify="space-between" wrap="nowrap">
            <Group gap="lg" wrap="nowrap">
              <Anchor href={nav.homeUrl}>
                <Image src={nav.logoUrl} alt={nav.logoAlt} h={25} w="auto" />
              </Anchor>
              <Group gap="md" visibleFrom="lg">
                <MainNav items={nav.main} />
              </Group>
            </Group>
            <Group gap="md" wrap="nowrap">
              <Group gap="md" visibleFrom="lg">
                <UserNav nav={nav} />
              </Group>
              <LocaleNav nav={nav} />
              <Burger opened={opened} onClick={toggle} hiddenFrom="lg" size="sm" />
            </Group>
          </Group>
        </Container>
      </AppShell.Header>

      <AppShell.Navbar p="md">
        <Stack gap="sm">
          {nav.main
            .flatMap((item) => (item.children.length > 0 ? item.children : [item]))
            .map((item) => (
              <NavAnchor key={item.href} item={item} c="dark" />
            ))}
          {nav.user.map((item) => (
            <NavAnchor key={item.href} item={item} c="dark" />
          ))}
        </Stack>
      </AppShell.Navbar>

      <AppShell.Main>
        <Container size="xl" py="md">
          {children}
        </Container>
        <Container size="xl" component="footer" py="xl">
          <SimpleGrid cols={{ base: 1, lg: 4 }}>
            {nav.footer.map((section) => (
              <Stack key={section.title ?? 'main'} gap={4} align="flex-start">
                {section.title && <Text fw={700}>{section.title}</Text>}
                {section.items.map((item) => (
                  <NavAnchor key={item.href} item={item} />
                ))}
              </Stack>
            ))}
          </SimpleGrid>
        </Container>
      </AppShell.Main>
    </AppShell>
  )
}
