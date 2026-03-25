<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Label } from '@/components/ui/label';
import { Trash2 } from 'lucide-vue-next';
import { Textarea } from '@/components/ui/textarea';
import { index, show } from '@/routes/posts';
import type { BreadcrumbItem } from '@/types';

interface User {
  id: number;
  name: string;
  email: string;
  is_admin?: boolean;
}

interface Comment {
  id: number;
  post_id: number;
  user_id: number;
  content: string;
  created_at: string;
  created_at_formatted?: string;
  user?: User | null;
}

interface Author {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
}

interface Post {
  id: number;
  title: string;
  content: string;
  author_id: number;
  published: boolean;
  created_at: string;
  updated_at: string;
  created_at_formatted: string;
  updated_at_formatted: string;
  author: Author;
  comments?: Comment[];
}

interface Props {
  post: Post;
  auth: {
    user: User;
  };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Posts', href: index().url },
  { title: props.post.title, href: show.url(props.post.id) },
];

const commentForm = useForm({
  content: '',
});

const submitComment = () => {
  commentForm.post(`/add-comment/${props.post.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      commentForm.reset();
    },
  });
};

const deleteComment = (commentId: number) => {
  if (!confirm('Kas oled kindel, et soovid selle kommentaari kustutada?')) return;
  
  router.delete(`/comments/${commentId}`, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Kommentaar kustutatud.');
    },
    onError: (err) => {
      console.error(err);
      alert('Kommentaari kustutamine ebaõnnestus.');
    },
  });
};

const canDeleteComment = (comment: Comment) => {
  return props.auth.user.id === comment.user_id || props.auth.user.is_admin;
};
</script>

<template>
  <Head :title="post.title" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-4xl mx-auto p-6 flex flex-col gap-6">
      <Card>
        <CardHeader>
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <CardTitle class="text-3xl">{{ post.title }}</CardTitle>
              <CardDescription class="mt-2">
                By {{ post.author.first_name }} {{ post.author.last_name }} •
                {{ post.created_at_formatted }}
              </CardDescription>
            </div>
            <Badge :variant="post.published ? 'default' : 'secondary'">
              {{ post.published ? 'Published' : 'Draft' }}
            </Badge>
          </div>
        </CardHeader>

        <CardContent>
          <div class="prose max-w-none">
            <p class="whitespace-pre-wrap">{{ post.content }}</p>
          </div>

          <Separator class="my-6" />

          <div class="text-sm text-muted-foreground">
            <p>Last updated: {{ post.updated_at_formatted }}</p>
          </div>
        </CardContent>
      </Card>

      <!-- Kommentaaride sektsioon -->
      <Card>
        <CardHeader>
          <CardTitle>Kommentaarid ({{ post.comments?.length || 0 }})</CardTitle>
        </CardHeader>

        <CardContent class="space-y-6">
          <!-- Kommentaaride nimekiri -->
          <div v-if="post.comments && post.comments.length > 0" class="space-y-4">
            <div
              v-for="comment in post.comments"
              :key="comment.id"
              class="border rounded-lg p-4 space-y-2"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <p class="font-semibold text-sm">
                    {{ comment.user?.name || 'Tundmatu kasutaja' }}
                  </p>
                  <p class="text-xs text-muted-foreground">
                    {{ comment.created_at_formatted || comment.created_at }}
                  </p>
                </div>
                <Button
                  v-if="canDeleteComment(comment)"
                  size="icon"
                  variant="ghost"
                  @click="deleteComment(comment.id)"
                  class="text-destructive hover:text-destructive"
                >
                  <Trash2 class="h-4 w-4" />
                </Button>
              </div>
              <p class="text-sm whitespace-pre-wrap">{{ comment.content }}</p>
            </div>
          </div>

          <div v-else class="text-center text-muted-foreground py-8">
            <p>Kommentaare pole veel lisatud.</p>
          </div>

          <Separator />

          <!-- Kommentaari lisamise vorm -->
          <form @submit.prevent="submitComment" class="space-y-4">
            <div>
              <Label for="comment">Lisa kommentaar</Label>
              <Textarea
                id="comment"
                v-model="commentForm.content"
                rows="4"
                placeholder="Kirjuta oma kommentaar siia..."
                :disabled="commentForm.processing"
              />
              <p v-if="commentForm.errors.content" class="text-red-600 text-sm mt-1">
                {{ commentForm.errors.content }}
              </p>
            </div>

            <Button type="submit" :disabled="commentForm.processing || !commentForm.content">
              Lisa kommentaar
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>