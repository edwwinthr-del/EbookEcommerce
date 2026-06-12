import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import BookForm, { type BookFormData, type CategoryOption } from './book-form';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Books', href: '/admin/books' },
    { title: 'Create', href: '/admin/books/create' },
];

export default function AdminBooksCreate({ categories }: { categories: CategoryOption[] }) {
    const { data, setData, post, errors, processing } = useForm<BookFormData>({
        title: '',
        author: '',
        description: '',
        price: '',
        category_id: '',
        accent_color: '#6366f1',
        status: 'draft',
        file: null,
        cover: null,
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New book" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-semibold">New book</h1>
                <BookForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    categories={categories}
                    submitLabel="Create book"
                    onSubmit={() => post('/admin/books', { forceFormData: true })}
                />
            </div>
        </AppLayout>
    );
}
