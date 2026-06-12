import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import BookForm, { type BookFormData, type CategoryOption } from './book-form';

interface EditableBook {
    id: number;
    title: string;
    author: string;
    description: string | null;
    price: string;
    category_id: number | null;
    accent_color: string | null;
    status: 'draft' | 'published';
    file_format: 'pdf' | 'epub';
    cover_url: string | null;
}

export default function AdminBooksEdit({ book, categories }: { book: EditableBook; categories: CategoryOption[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin' },
        { title: 'Books', href: '/admin/books' },
        { title: book.title, href: `/admin/books/${book.id}/edit` },
    ];

    const { data, setData, post, errors, processing } = useForm<BookFormData & { _method: string }>({
        _method: 'put',
        title: book.title,
        author: book.author,
        description: book.description ?? '',
        price: String(book.price),
        category_id: book.category_id ? String(book.category_id) : '',
        accent_color: book.accent_color ?? '#6366f1',
        status: book.status,
        file: null,
        cover: null,
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit: ${book.title}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-semibold">Edit book</h1>
                <BookForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    categories={categories}
                    submitLabel="Save changes"
                    onSubmit={() => post(`/admin/books/${book.id}`, { forceFormData: true })}
                    currentFileFormat={book.file_format}
                    currentCoverUrl={book.cover_url}
                />
            </div>
        </AppLayout>
    );
}
