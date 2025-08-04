<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Simon Chester <simon.chester@totara.com>
  @module core_mfa
-->

<template>
  <div class="tui-auth_ssosaml-verify">
    <div class="tui-auth_ssosaml-verify__col">
      <div class="tui-auth_ssosaml-verify__contentWrapper">
        <transition :name="'tui-auth_ssosaml-verify__transition-' + transition">
          <div
            v-if="step === 'choose'"
            key="choose"
            class="tui-auth_ssosaml-verify__screen"
          >
            <h1 class="tui-auth_ssosaml-verify__title">
              {{ $str('two_factor_verification', 'mfa') }}
            </h1>
            <div class="tui-auth_ssosaml-verify__content">
              <div class="tui-auth_ssosaml-verify__desc">
                {{ $str('choose_desc', 'mfa') }}
              </div>
              <div class="tui-auth_ssosaml-verify__verificationOptions">
                <FactorChooser
                  :factors="registeredFactors"
                  :loading-factor="loadingFactor"
                  @select="handleFactorSelect"
                />
              </div>
              <div>
                <Button :text="$str('cancel', 'core')" @click="cancel" />
              </div>
            </div>
          </div>

          <div
            v-else-if="step === 'verify'"
            key="verify"
            class="tui-auth_ssosaml-verify__screen"
          >
            <h1 class="tui-auth_ssosaml-verify__title">
              {{ factor.verifyTitle }}
            </h1>

            <div class="tui-auth_ssosaml-verify__content">
              <div>
                <component
                  :is="factorComponent"
                  :submitting="submitting"
                  :data="factor.data"
                  :submission-error="submissionError"
                  @submit="handleSubmit"
                  @cancel="cancel"
                />
              </div>

              <div class="tui-auth_ssosaml-verify__verify-alternatives">
                <Button
                  v-if="registeredFactors.length > 1"
                  :text="$str('use_a_different_factor', 'mfa')"
                  :styleclass="{ transparent: true }"
                  :disabled="submitting"
                  @click="backToChoose"
                />
                <Button
                  v-else
                  :text="$str('cancel', 'core')"
                  :styleclass="{ transparent: true }"
                  :disabled="submitting"
                  @click="cancel"
                />
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script>
import tui from 'tui/tui';
import Button from 'tui/components/buttons/Button';
import ButtonAria from 'tui/components/buttons/ButtonAria';
import FactorChooser from 'core_mfa/components/factors/FactorChooser';
import verifyMutation from 'core/graphql/mfa_verify_factor';

export default {
  components: {
    Button,
    ButtonAria,
    FactorChooser,
  },

  props: {
    registeredFactors: Array,
  },

  data() {
    return {
      step: 'choose',
      loadingFactor: null,
      factor: null,
      factorComponent: null,
      transition: 'next',
      submitting: false,
      submissionError: null,
    };
  },

  created() {
    if (this.registeredFactors.length === 1) {
      this.factor = this.registeredFactors[0];
      this.step = 'verify';
      this.factorComponent = tui.asyncComponent(
        this.verifyComponentPath(this.factor.id)
      );
    }
  },

  methods: {
    async handleFactorSelect(factor) {
      this.loadingFactor = factor.id;

      this.factorComponent = await tui.loadComponent(
        this.verifyComponentPath(factor.id)
      );
      this.factor = factor;
      this.submissionError = null;
      this.submitting = false;
      this.loadingFactor = null;
      this.transition = 'next';
      this.step = 'verify';
    },

    backToChoose() {
      this.factorComponent = null;
      this.factor = null;
      this.submissionError = null;
      this.submitting = false;
      this.transition = 'back';
      this.step = 'choose';
    },

    async handleSubmit(data) {
      this.submitting = true;
      const factor = this.factor;
      try {
        const result = await this.$apollo.mutate({
          mutation: verifyMutation,
          variables: {
            input: {
              factor: this.factor.id,
              data: JSON.stringify(data),
            },
          },
        });
        if (this.factor !== factor) {
          // factor changed
          return;
        }
        if (result.data.result.success) {
          this.submissionError = null;
          window.location = result.data.result.next_url;

          // handle back button
          const handler = event => {
            if (event.persisted) {
              this.submitting = false;
              window.removeEventListener('pageshow', handler);
            }
          };
          window.addEventListener('pageshow', handler);
        } else {
          this.submissionError = {
            type: 'verify',
            message: this.$str('error:factor_verification', 'mfa'),
          };
          this.submitting = false;
        }
      } catch (e) {
        this.submitting = false;
        throw e;
      }
    },

    cancel() {
      window.location = this.$url('/login/logout.php');
    },

    verifyComponentPath(factor) {
      return `mfa_${factor}/components/Verify`;
    },
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-verify {
  @include font(body);
  display: flex;
  justify-content: center;
  padding-top: var(--gap-4);
  padding-bottom: var(--gap-12);

  &__col {
    display: flex;
    flex: 0 1 rem-px(500);
    flex-direction: column;
    gap: var(--gap-4);
  }

  &__contentWrapper {
    position: relative;
  }

  &__screen {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
  }

  &__content {
    display: flex;
    flex-direction: column;
    gap: var(--gap-6);
  }

  &__title {
    margin: 0;
  }

  &__verify-alternatives {
    display: flex;
    flex-direction: column;
    gap: var(--gap-3);
    align-items: flex-start;
  }

  &__transition-next,
  &__transition-back {
    &-enter-active,
    &-leave-active {
      $time: 0.25s;
      $fn: cubic-bezier(0, 0.1, 0, 1);
      transition: transform $time $fn, opacity $time $fn;
    }
    &-enter,
    &-leave-active {
      opacity: 0;
    }
    &-leave-active {
      position: absolute;
    }
    &-enter {
      transform: translateX(rem-px(500));
    }
    &-leave-active {
      transform: translateX(rem-px(-500));
    }
  }

  &__transition-back {
    &-enter {
      transform: translateX(rem-px(-500));
    }
    &-leave-active {
      transform: translateX(rem-px(500));
    }
  }

  @media (prefers-reduced-motion: reduce) {
    &__transition-next,
    &__transition-back {
      &-enter-active,
      &-leave-active {
        // stylelint-disable-next-line time-min-milliseconds
        transition-duration: 0.001ms;
      }
    }
  }
}
</style>
