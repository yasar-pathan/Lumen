# Lumen AI - Easy AWS Deployment Guide (GitHub Actions + ECS Express Mode)

If you do not have **Docker Desktop** or the **AWS CLI** installed locally on your machine, you can automate the entire build-and-push process using **GitHub Actions**. 

This means GitHub's cloud servers will build your container and push it to AWS ECR for you.

---

## 3-Step Deployment Plan

### Step 1: Add AWS Secrets to GitHub
To let GitHub securely push the code to your AWS account:
1. Go to your GitHub Repository: `yasar-pathan/Lumen`.
2. Click **Settings** ➔ **Secrets and variables** ➔ **Actions**.
3. Click **New repository secret** and add:
   * `AWS_ACCESS_KEY_ID`: Your AWS access key.
   * `AWS_SECRET_ACCESS_KEY`: Your AWS secret access key.

---

### Step 2: Push to GitHub (Triggers Auto-Build)
I have created a workflow file at `.github/workflows/deploy.yml` in your project.

Simply **commit and push** your changes to your repository's `main` branch. GitHub Actions will automatically start building the Docker image and push it to Amazon ECR. 

You can watch the build progress live in the **Actions** tab of your repository. Once completed, your ECR Image URI will look like:
`123456789012.dkr.ecr.us-east-1.amazonaws.com/lumen-observability:latest` (replace `123456789012` with your AWS Account ID).

---

### Step 3: Deploy to ECS Express Mode
1. Open the **Amazon ECS Console** and select **Express Mode** from the left-hand navigation pane.
2. In the setup form:
   * **Container Image URI:** Paste your ECR Image URI from Step 2.
   * **Port Mappings:** Add Port `80` (TCP).
   * **Environment Variables:** Add your database credentials so Laravel can talk to Supabase:
     * `DB_CONNECTION=pgsql`
     * `DB_HOST=db.ynqsuzmcnbkqppjctxfo.supabase.co`
     * `DB_PORT=5432`
     * `DB_DATABASE=postgres`
     * `DB_USERNAME=postgres`
     * `DB_PASSWORD=Hello@yasar123`
     * `APP_KEY=base64:NM0oWW+z4r9zGfr4yxbBZm9M4kZ2ZcerHXO7UOw53eU=`
     * `APP_ENV=production`
     * `APP_DEBUG=false`
3. Click **Create**.

---

### That's It! 🎉
AWS ECS will automatically boot your container on AWS Fargate, configure the load balancer, enable HTTPS, and give you a fully functional public URL to test your application at `https://<your-load-balancer-dns>/app`!
