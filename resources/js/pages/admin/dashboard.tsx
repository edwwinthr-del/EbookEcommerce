import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { BookOpen, Receipt, Wallet } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
];

interface Stats {
    books: number;
    orders: number;
    revenue: number;
}

export default function AdminDashboard({ stats }: { stats: Stats }) {
    const cards = [
        { label: 'Books', value: stats.books, icon: BookOpen, href: '/admin/books' },
        { label: 'Orders', value: stats.orders, icon: Receipt, href: '/admin/orders' },
        { label: 'Revenue', value: `$${stats.revenue.toFixed(2)}`, icon: Wallet, href: '/admin/orders' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-semibold">Admin dashboard</h1>
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    {cards.map((card) => (
                        <Link
                            key={card.label}
                            href={card.href}
                            className="border-sidebar-border/70 dark:border-sidebar-border flex flex-col gap-2 rounded-xl border p-6 transition-colors hover:bg-muted/50"
                        >
                            <div className="flex items-center gap-2 text-muted-foreground">
                                <card.icon className="size-4" />
                                <span className="text-sm">{card.label}</span>
                            </div>
                            <span className="text-3xl font-semibold">{card.value}</span>
                        </Link>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
