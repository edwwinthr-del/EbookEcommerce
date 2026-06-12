import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export interface CategoryOption {
    id: number;
    name: string;
}

export interface BookFormData {
    title: string;
    author: string;
    description: string;
    price: string;
    category_id: string;
    accent_color: string;
    status: string;
    file: File | null;
    cover: File | null;
    [key: string]: string | File | null;
}

interface BookFormProps {
    data: BookFormData;
    setData: (key: string, value: string | File | null) => void;
    errors: Partial<Record<string, string>>;
    processing: boolean;
    categories: CategoryOption[];
    submitLabel: string;
    onSubmit: () => void;
    currentFileFormat?: string;
    currentCoverUrl?: string | null;
}

const selectClasses =
    'border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:ring-1 focus-visible:outline-none dark:bg-input/30';

export default function BookForm({
    data,
    setData,
    errors,
    processing,
    categories,
    submitLabel,
    onSubmit,
    currentFileFormat,
    currentCoverUrl,
}: BookFormProps) {
    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                onSubmit();
            }}
            className="max-w-2xl space-y-6"
        >
            <div className="grid gap-2">
                <Label htmlFor="title">Title</Label>
                <Input id="title" value={data.title} onChange={(e) => setData('title', e.target.value)} required />
                <InputError message={errors.title} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="author">Author</Label>
                <Input id="author" value={data.author} onChange={(e) => setData('author', e.target.value)} required />
                <InputError message={errors.author} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="description">Description</Label>
                <textarea
                    id="description"
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                    rows={5}
                    className="border-input w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:ring-1 focus-visible:outline-none dark:bg-input/30"
                />
                <InputError message={errors.description} />
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="price">Price (USD)</Label>
                    <Input
                        id="price"
                        type="number"
                        min="0"
                        step="0.01"
                        value={data.price}
                        onChange={(e) => setData('price', e.target.value)}
                        required
                    />
                    <InputError message={errors.price} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="category_id">Category</Label>
                    <select
                        id="category_id"
                        value={data.category_id}
                        onChange={(e) => setData('category_id', e.target.value)}
                        className={selectClasses}
                    >
                        <option value="">— none —</option>
                        {categories.map((category) => (
                            <option key={category.id} value={category.id}>
                                {category.name}
                            </option>
                        ))}
                    </select>
                    <InputError message={errors.category_id} />
                </div>
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="accent_color">Accent color</Label>
                    <input
                        id="accent_color"
                        type="color"
                        value={data.accent_color || '#6366f1'}
                        onChange={(e) => setData('accent_color', e.target.value)}
                        className="h-9 w-20 cursor-pointer rounded-md border border-input bg-transparent p-1"
                    />
                    <InputError message={errors.accent_color} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <select id="status" value={data.status} onChange={(e) => setData('status', e.target.value)} className={selectClasses}>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                    <InputError message={errors.status} />
                </div>
            </div>

            <div className="grid gap-2">
                <Label htmlFor="cover">Cover image {currentCoverUrl ? '(replace current)' : ''}</Label>
                {currentCoverUrl && <img src={currentCoverUrl} alt="Current cover" className="h-24 w-auto rounded-md border" />}
                <Input id="cover" type="file" accept="image/*" onChange={(e) => setData('cover', e.target.files?.[0] ?? null)} />
                <InputError message={errors.cover} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="file">
                    E-book file (PDF or EPUB, max 50 MB)
                    {currentFileFormat ? ` — current: ${currentFileFormat.toUpperCase()}, upload to replace` : ''}
                </Label>
                <Input id="file" type="file" accept=".pdf,.epub" onChange={(e) => setData('file', e.target.files?.[0] ?? null)} />
                <InputError message={errors.file} />
            </div>

            <Button disabled={processing}>{submitLabel}</Button>
        </form>
    );
}
