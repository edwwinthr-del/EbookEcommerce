import { Book3D, type CatalogBook } from '@/components/book-card';
import { Button } from '@/components/ui/button';
import StoreLayout from '@/layouts/store-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Library } from 'lucide-react';
import { useState } from 'react';

interface BookDetail extends CatalogBook {
    description: string | null;
    file_format: 'pdf' | 'epub';
}

export default function CatalogShow({ book, owned }: { book: BookDetail; owned: boolean }) {
    const [processing, setProcessing] = useState(false);

    const buy = () => {
        router.post(
            `/checkout/${book.id}`,
            {},
            {
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <StoreLayout>
            <Head title={book.title} />

            <div className="grid gap-8 md:grid-cols-[280px_1fr]">
                <div className="w-full max-w-[280px]">
                    <Book3D book={book} maxTilt={10} />
                </div>

                <div className="flex flex-col gap-4">
                    {book.category && <span className="text-sm text-muted-foreground">{book.category.name}</span>}
                    <div>
                        <h1 className="font-serif text-4xl font-semibold">{book.title}</h1>
                        <p className="mt-1 text-lg text-muted-foreground">by {book.author}</p>
                    </div>

                    <p className="text-2xl font-semibold">${book.price}</p>

                    <p className="text-sm text-muted-foreground uppercase">{book.file_format}</p>

                    {owned ? (
                        <Button asChild className="w-fit">
                            <Link href="/library">
                                <Library className="size-4" />
                                In your library
                            </Link>
                        </Button>
                    ) : (
                        <Button onClick={buy} disabled={processing} className="w-fit">
                            {processing ? 'Heading to checkout…' : `Buy for $${book.price}`}
                        </Button>
                    )}

                    {book.description && <div className="prose dark:prose-invert mt-4 max-w-none whitespace-pre-line">{book.description}</div>}
                </div>
            </div>
        </StoreLayout>
    );
}
