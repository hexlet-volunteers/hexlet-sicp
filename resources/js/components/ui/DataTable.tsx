import { Table, type TableProps, Text } from '@mantine/core'

export type Column<T> = {
  label: string
  render: (item: T) => React.ReactNode
}

type Props<T> = {
  columns: Column<T>[]
  items: T[]
  empty?: string
} & TableProps

export function DataTable<T extends { id: number }>({ columns, items, empty, ...tableProps }: Props<T>) {
  return (
    <Table.ScrollContainer minWidth={600}>
      <Table {...tableProps}>
        <Table.Thead>
          <Table.Tr>
            {columns.map((column) => (
              <Table.Th key={column.label}>{column.label}</Table.Th>
            ))}
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody>
          {items.length === 0 && empty ? (
            <Table.Tr>
              <Table.Td colSpan={columns.length}>
                <Text c="dimmed" ta="center" py="md">
                  {empty}
                </Text>
              </Table.Td>
            </Table.Tr>
          ) : (
            items.map((item) => (
              <Table.Tr key={item.id}>
                {columns.map((column) => (
                  <Table.Td key={column.label}>{column.render(item)}</Table.Td>
                ))}
              </Table.Tr>
            ))
          )}
        </Table.Tbody>
      </Table>
    </Table.ScrollContainer>
  )
}
