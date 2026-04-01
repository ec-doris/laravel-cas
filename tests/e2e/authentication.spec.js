import { expect, test } from '@playwright/test';

const credentials = {
  username: 'casuser',
  password: 'Mellon',
};

async function loginThroughCas(page) {
  await page.goto('/dashboard');
  await expect(page).toHaveURL(/127\.0\.0\.1:9800\/cas\/login/);
  await expect(page.getByRole('heading', { name: 'Local CAS Login' })).toBeVisible();

  await page.getByLabel('Username').fill(credentials.username);
  await page.getByLabel('Password').fill(credentials.password);
  await page.getByRole('button', { name: 'Sign in' }).click();

  await expect(page).toHaveURL(/\/dashboard$/);
  await expect(page.getByRole('heading', { name: 'CAS Dashboard' })).toBeVisible();
}

test('authenticates through the CAS callback and persists mapped attributes', async ({ page }) => {
  await loginThroughCas(page);

  await expect(page.getByText('cas.user@example.test')).toBeVisible();
  await expect(page.locator('dd').filter({ hasText: 'DIGIT.A.4' })).toHaveCount(3);

  const whoami = await page.evaluate(async () => {
    const response = await fetch('/whoami');

    return response.json();
  });

  expect(whoami).toMatchObject({
    email: 'cas.user@example.test',
    departmentNumber: 'DIGIT.A.4',
    department_number: 'DIGIT.A.4',
    organisation: 'DIGIT.A.4',
  });
});

test('logs out through CAS and returns to the guest home page', async ({ page }) => {
  await loginThroughCas(page);

  await page.getByRole('link', { name: 'Log out' }).click();

  await expect(page).toHaveURL('http://127.0.0.1:8001/');
  await expect(page.getByRole('heading', { name: 'Laravel CAS Workbench' })).toBeVisible();
  await expect(page.getByText('Guest')).toBeVisible();

  await page.goto('/dashboard');
  await expect(page).toHaveURL(/127\.0\.0\.1:9800\/cas\/login/);
});
