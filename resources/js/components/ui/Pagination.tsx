import { Link } from '@inertiajs/react'
import { Button, Group } from '@mantine/core'

function PageLink({ link }: { link: App.DTO.PaginationLinkData }) {
  if (link.url === null) {
    return (
      <Button variant="default" size="compact-md" disabled>
        {link.label}
      </Button>
    )
  }

  return (
    <Button
      component={Link}
      href={link.url}
      variant={link.active ? 'filled' : 'default'}
      size="compact-md"
      aria-current={link.active ? 'page' : undefined}
    >
      {link.label}
    </Button>
  )
}

// Рендерит links[] пагинатора как есть: URL страниц приходят с бэкенда (ADR 0002),
// поэтому не Mantine <Pagination> — тот собирает ?page=N в JS.
export function Pagination({ pagination }: { pagination: App.DTO.PaginationData }) {
  if (pagination.lastPage <= 1) {
    return null
  }

  return (
    <Group gap={4} mt="md" component="nav">
      {pagination.links.map((link, index) => (
        // biome-ignore lint/suspicious/noArrayIndexKey: список не переупорядочивается, а «...» бывает дважды
        <PageLink key={index} link={link} />
      ))}
    </Group>
  )
}
