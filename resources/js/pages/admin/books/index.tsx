import Pagination from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Books', href: '/admin/books' },
];

interface BookRow {
    id: number;
    title: string;
    author: string;
    price: string;
    status: 'draft' | 'published';
    category: string | null;
}

export default function AdminBooksIndex({ books }: { books: Paginated<BookRow> }) {
    const destroy = (book: BookRow) => {
        if (confirm(`Delete "${book.title}"? This also removes its files.`)) {
            router.delete(`/admin/books/${book.id}`, { preserveScroll: true });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Books" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Books</h1>
                    <Button asChild>
                        <Link href="/admin/books/create">New book</Link>
                    </Button>
                </div>

                <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="px-4 py-3 font-medium">Title</th>
                                <th className="px-4 py-3 font-medium">Author</th>
                                <th className="px-4 py-3 font-medium">Category</th>
                                <th className="px-4 py-3 font-medium">Price</th>
                                <th className="px-4 py-3 font-medium">Status</th>
                                <th className="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {books.data.map((book) => (
                                <tr key={book.id} className="border-b last:border-0">
                                    <td className="px-4 py-3 font-medium">{book.title}</td>
                                    <td className="px-4 py-3">{book.author}</td>
                                    <td className="px-4 py-3">{book.category ?? '—'}</td>
                                    <td className="px-4 py-3">${book.price}</td>
                                    <td className="px-4 py-3">
                                        <Badge variant={book.status === 'published' ? 'default' : 'secondary'}>{book.status}</Badge>
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex gap-2">
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={`/admin/books/${book.id}/edit`}>Edit</Link>
                                            </Button>
                                            <Button variant="destructive" size="sm" onClick={() => destroy(book)}>
                                                Delete
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {books.data.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="px-4 py-8 text-center text-muted-foreground">
                                        No books yet.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination links={books.links} />
            </div>
        </AppLayout>
    );
}
