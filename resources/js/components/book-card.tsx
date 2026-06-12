import { Link } from '@inertiajs/react';

export interface CatalogBook {
    id: number;
    title: string;
    author: string;
    slug: string;
    price: string;
    accent_color: string | null;
    cover_url: string | null;
    category: { name: string; slug: string } | null;
}

export function BookCover({ book, className = '' }: { book: CatalogBook; className?: string }) {
    if (book.cover_url) {
        return <img src={book.cover_url} alt={`Cover of ${book.title}`} className={`h-full w-full object-cover ${className}`} />;
    }

    return (
        <div
            className={`flex h-full w-full items-center justify-center p-4 text-center ${className}`}
            style={{ backgroundColor: book.accent_color ?? '#6366f1' }}
        >
            <span className="font-serif text-lg font-semibold text-white [text-shadow:0_1px_2px_rgba(0,0,0,0.4)]">{book.title}</span>
        </div>
    );
}

export default function BookCard({ book }: { book: CatalogBook }) {
    return (
        <Link href={`/books/${book.slug}`} className="group flex flex-col gap-3">
            <div className="aspect-2/3 overflow-hidden rounded-lg border shadow-sm transition-shadow group-hover:shadow-md">
                <BookCover book={book} />
            </div>
            <div>
                <h3 className="font-medium leading-tight group-hover:underline">{book.title}</h3>
                <p className="text-sm text-muted-foreground">{book.author}</p>
                <p className="mt-1 text-sm font-semibold">${book.price}</p>
            </div>
        </Link>
    );
}
