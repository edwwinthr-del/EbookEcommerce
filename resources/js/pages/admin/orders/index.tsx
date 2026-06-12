import Pagination from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Paginated } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Orders', href: '/admin/orders' },
];

interface OrderRow {
    id: number;
    customer: string;
    email: string;
    items: { title: string; price: string }[];
    total: string;
    status: 'pending' | 'paid' | 'failed';
    created_at: string;
}

const statusVariant = {
    paid: 'default',
    pending: 'secondary',
    failed: 'destructive',
} as const;

export default function AdminOrdersIndex({ orders }: { orders: Paginated<OrderRow> }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Orders" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-semibold">Orders</h1>

                <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="px-4 py-3 font-medium">#</th>
                                <th className="px-4 py-3 font-medium">Customer</th>
                                <th className="px-4 py-3 font-medium">Items</th>
                                <th className="px-4 py-3 font-medium">Total</th>
                                <th className="px-4 py-3 font-medium">Status</th>
                                <th className="px-4 py-3 font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            {orders.data.map((order) => (
                                <tr key={order.id} className="border-b last:border-0">
                                    <td className="px-4 py-3">{order.id}</td>
                                    <td className="px-4 py-3">
                                        <div className="font-medium">{order.customer}</div>
                                        <div className="text-muted-foreground">{order.email}</div>
                                    </td>
                                    <td className="px-4 py-3">
                                        {order.items.map((item, index) => (
                                            <div key={index}>
                                                {item.title} <span className="text-muted-foreground">(${item.price})</span>
                                            </div>
                                        ))}
                                    </td>
                                    <td className="px-4 py-3 font-medium">${order.total}</td>
                                    <td className="px-4 py-3">
                                        <Badge variant={statusVariant[order.status]}>{order.status}</Badge>
                                    </td>
                                    <td className="px-4 py-3 text-muted-foreground">{order.created_at}</td>
                                </tr>
                            ))}
                            {orders.data.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="px-4 py-8 text-center text-muted-foreground">
                                        No orders yet.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination links={orders.links} />
            </div>
        </AppLayout>
    );
}
