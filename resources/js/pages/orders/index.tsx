import Pagination from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Paginated } from '@/types';
import { Head, Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'My Orders', href: '/orders' }];

interface CustomerOrder {
    id: number;
    total: string;
    status: 'pending' | 'paid' | 'failed';
    created_at: string;
    items: { title: string; slug: string | null; price: string }[];
}

const statusVariant = {
    paid: 'default',
    pending: 'secondary',
    failed: 'destructive',
} as const;

export default function OrdersIndex({ orders }: { orders: Paginated<CustomerOrder> }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Orders" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-semibold">My Orders</h1>

                {orders.data.length > 0 ? (
                    <div className="flex flex-col gap-4">
                        {orders.data.map((order) => (
                            <div key={order.id} className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <div className="flex items-center gap-3">
                                        <span className="font-medium">Order #{order.id}</span>
                                        <Badge variant={statusVariant[order.status]}>{order.status}</Badge>
                                    </div>
                                    <span className="text-sm text-muted-foreground">{order.created_at}</span>
                                </div>
                                <ul className="mt-3 space-y-1 text-sm">
                                    {order.items.map((item, index) => (
                                        <li key={index} className="flex justify-between">
                                            {item.slug ? (
                                                <Link href={`/books/${item.slug}`} className="hover:underline">
                                                    {item.title}
                                                </Link>
                                            ) : (
                                                <span>{item.title}</span>
                                            )}
                                            <span>${item.price}</span>
                                        </li>
                                    ))}
                                </ul>
                                <div className="mt-3 flex justify-end border-t pt-3 text-sm font-semibold">Total: ${order.total}</div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="py-16 text-center text-muted-foreground">
                        <p className="text-lg">No orders yet.</p>
                    </div>
                )}

                <Pagination links={orders.links} />
            </div>
        </AppLayout>
    );
}
