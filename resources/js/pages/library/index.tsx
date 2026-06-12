import { BookCover, type CatalogBook } from '@/components/book-card';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Download } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'My Library', href: '/library' }];

interface LibraryBook extends Omit<CatalogBook, 'price'> {
    file_format: 'pdf' | 'epub';
}

export default function LibraryIndex({ books }: { books: LibraryBook[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Library" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-semibold">My Library</h1>

                {books.length > 0 ? (
                    <div className="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
                        {books.map((book) => (
                            <div key={book.id} className="flex flex-col gap-3">
                                <Link href={`/books/${book.slug}`} className="aspect-2/3 overflow-hidden rounded-lg border shadow-sm">
                                    <BookCover book={{ ...book, price: '' }} />
                                </Link>
                                <div>
                                    <h3 className="font-medium leading-tight">{book.title}</h3>
                                    <p className="text-sm text-muted-foreground">{book.author}</p>
                                </div>
                                <Button asChild size="sm" variant="outline">
                                    <a href={`/library/${book.id}/download`}>
                                        <Download className="size-4" />
                                        Download {book.file_format.toUpperCase()}
                                    </a>
                                </Button>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="flex flex-col items-center gap-3 py-16 text-center text-muted-foreground">
                        <p className="text-lg">Your library is feeling a little empty.</p>
                        <p className="text-sm">Every great collection starts with one book.</p>
                        <Button asChild className="mt-2">
                            <Link href="/">Browse the catalog</Link>
                        </Button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
