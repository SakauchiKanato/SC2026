/**
 * Area関連のAPIクライアント
 * バックエンド（生PHP）の /api/areas, /api/feature-tags エンドポイントを呼び出す
 */

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? '/api';

/**
 * レスポンスを共通処理する
 * エラー時はメッセージ・バリデーションエラーを含めて例外を投げる
 */
async function handleResponse(response) {
  const contentType = response.headers.get('content-type') ?? '';
  const body = contentType.includes('application/json') ? await response.json() : null;

  if (!response.ok) {
    const error = new Error(body?.error ?? 'APIリクエストに失敗しました');
    error.status = response.status;
    error.errors = body?.errors ?? null;
    throw error;
  }

  return body;
}

export async function fetchAreas() {
  const response = await fetch(`${API_BASE_URL}/areas`);
  return handleResponse(response);
}

export async function fetchArea(id) {
  const response = await fetch(`${API_BASE_URL}/areas/${id}`);
  return handleResponse(response);
}

export async function createArea(payload) {
  const response = await fetch(`${API_BASE_URL}/areas`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  return handleResponse(response);
}

export async function updateArea(id, payload) {
  const response = await fetch(`${API_BASE_URL}/areas/${id}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  return handleResponse(response);
}

export async function deleteArea(id) {
  const response = await fetch(`${API_BASE_URL}/areas/${id}`, {
    method: 'DELETE',
  });

  if (!response.ok) {
    const error = new Error('削除に失敗しました');
    error.status = response.status;
    throw error;
  }
}

/**
 * 特色タグの選択肢一覧を取得する（登録・編集フォームのプルダウン用）
 * 誰かが新しいタグを登録すると、以後このAPIの結果に含まれるようになる
 */
export async function fetchFeatureTags() {
  const response = await fetch(`${API_BASE_URL}/feature-tags`);
  return handleResponse(response);
}